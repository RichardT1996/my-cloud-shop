import logging
import json
import os
import struct
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Login function triggered')
    
    try:
        req_body = req.get_json()
        email = req_body.get('email')
        password = req_body.get('password')
        
        if not email or not password:
            return func.HttpResponse(
                json.dumps({"success": False, "error": "Email and password required"}),
                status_code=400,
                mimetype="application/json"
            )
        
        logging.info(f'Attempting login for: {email}')
        
        # Connect to database
        try:
            conn = get_db_connection()
            logging.info('Database connection established')
        except Exception as e:
            logging.error(f'Database connection failed: {str(e)}')
            return func.HttpResponse(
                json.dumps({"success": False, "error": f"Database connection error: {str(e)}"}),
                status_code=500,
                mimetype="application/json"
            )
        
        cursor = conn.cursor()
        
        # Query user
        cursor.execute(
            "SELECT id, name, email, password FROM shopusers WHERE email = %s",
            (email,)
        )
        user = cursor.fetchone()
        conn.close()
        
        if not user:
            logging.warning(f'User not found: {email}')
            return func.HttpResponse(
                json.dumps({"success": False, "error": "Invalid credentials"}),
                status_code=401,
                mimetype="application/json"
            )
        
        # Return user data
        user_data = {
            "success": True,
            "user": {
                "id": user['id'],
                "name": user['name'],
                "email": user['email'],
                "is_admin": (email == "admin@gmail.com")
            },
            "hashed_password": user['password']
        }
        
        logging.info(f'Login successful for: {email}')
        return func.HttpResponse(
            json.dumps(user_data),
            status_code=200,
            mimetype="application/json"
        )
        
    except Exception as e:
        logging.error(f'Error: {str(e)}')
        import traceback
        logging.error(traceback.format_exc())
        return func.HttpResponse(
            json.dumps({"success": False, "error": str(e)}),
            status_code=500,
            mimetype="application/json"
        )

def get_db_connection():
    """Create database connection using pymysql"""
    import pymysql
    
    host = os.environ.get('DB_HOST', 'localhost')
    database = os.environ.get('DB_NAME', 'shopsphere_db')
    username = os.environ.get('DB_USER', 'root')
    password = os.environ.get('DB_PASS', 'password')
    port = int(os.environ.get('DB_PORT', '3306'))
    
    logging.info(f'Connecting to MySQL at {host}:{port}')
    
    return pymysql.connect(
        host=host,
        port=port,
        user=username,
        password=password,
        database=database,
        cursorclass=pymysql.cursors.DictCursor
    )
