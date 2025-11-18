import azure.functions as func
import logging
import json
import os
import pyodbc

app = func.FunctionApp()

# Get DB connection from environment variables
def get_db_connection():
    server = os.environ.get('DB_SERVER', 'tcp:mycardiffmet1.database.windows.net,1433')
    database = os.environ.get('DB_NAME', 'myDatabase')
    username = os.environ.get('DB_USER', 'myadmin')
    password = os.environ.get('DB_PASS', 'password123!')
    
    conn_str = f'DRIVER={{ODBC Driver 18 for SQL Server}};SERVER={server};DATABASE={database};UID={username};PWD={password};Encrypt=yes;TrustServerCertificate=no'
    return pyodbc.connect(conn_str)

@app.function_name(name="Login")
@app.route(route="login", methods=["POST"], auth_level=func.AuthLevel.ANONYMOUS)
def login(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Login function triggered')
    
    try:
        # Parse request body
        req_body = req.get_json()
        email = req_body.get('email', '').strip()
        password = req_body.get('password', '')
        
        if not email or not password:
            return func.HttpResponse(
                json.dumps({'error': 'Email and password are required'}),
                status_code=400,
                mimetype='application/json'
            )
        
        # Query database for user
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute(
            "SELECT id, name, email, password FROM shopusers WHERE email = ?",
            (email,)
        )
        user = cursor.fetchone()
        
        if not user:
            conn.close()
            return func.HttpResponse(
                json.dumps({'error': 'Invalid credentials'}),
                status_code=401,
                mimetype='application/json'
            )
        
        user_id, name, user_email, hashed_password = user
        
        # Note: Password verification will be done in PHP
        # This function just checks if user exists and returns the hashed password
        # PHP will verify using password_verify()
        
        conn.close()
        
        # Return user data
        response_data = {
            'success': True,
            'user': {
                'id': user_id,
                'name': name,
                'email': user_email,
                'is_admin': user_email == 'admin@gmail.com'
            },
            'hashed_password': hashed_password  # PHP will verify this
        }
        
        return func.HttpResponse(
            json.dumps(response_data),
            status_code=200,
            mimetype='application/json'
        )
        
    except ValueError as e:
        logging.error(f'Invalid JSON: {e}')
        return func.HttpResponse(
            json.dumps({'error': 'Invalid JSON'}),
            status_code=400,
            mimetype='application/json'
        )
    except Exception as e:
        logging.error(f'Login error: {e}')
        return func.HttpResponse(
            json.dumps({'error': f'Internal server error: {str(e)}'}),
            status_code=500,
            mimetype='application/json'
        )
