import logging
import json
import os
import pyodbc
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Login function triggered.')
    logging.info(f'Request method: {req.method}')
    logging.info(f'Request body: {req.get_body()}')

    try:
        # Parse request body
        req_body = req.get_json()
        logging.info(f'Parsed JSON: {req_body}')
        email = req_body.get('email')
        password = req_body.get('password')
        logging.info(f'Email: {email}, Password received: {bool(password)}')

        if not email or not password:
            logging.warning('Missing email or password')
            return func.HttpResponse(
                json.dumps({"success": False, "error": "Email and password required"}),
                status_code=400,
                mimetype="application/json"
            )

        # Get database connection
        logging.info('Attempting database connection...')
        conn = get_db_connection()
        logging.info('Database connected successfully')
        cursor = conn.cursor()

        # Query user
        cursor.execute(
            "SELECT id, name, email, password FROM shopusers WHERE email = ?",
            (email,)
        )
        user = cursor.fetchone()
        
        conn.close()

        if not user:
            return func.HttpResponse(
                json.dumps({"success": False, "error": "Invalid credentials"}),
                status_code=401,
                mimetype="application/json"
            )

        # Return user data and hashed password for PHP to verify
        user_data = {
            "success": True,
            "user": {
                "id": user[0],
                "name": user[1],
                "email": user[2],
                "is_admin": (email == "admin@gmail.com")
            },
            "hashed_password": user[3]
        }

        return func.HttpResponse(
            json.dumps(user_data),
            status_code=200,
            mimetype="application/json"
        )

    except ValueError:
        return func.HttpResponse(
            json.dumps({"success": False, "error": "Invalid JSON"}),
            status_code=400,
            mimetype="application/json"
        )
    except Exception as e:
        logging.error(f"Error: {str(e)}")
        return func.HttpResponse(
            json.dumps({"success": False, "error": "Internal server error"}),
            status_code=500,
            mimetype="application/json"
        )

def get_db_connection():
    """Create database connection using environment variables"""
    try:
        server = os.environ.get('DB_SERVER')
        database = os.environ.get('DB_NAME')
        username = os.environ.get('DB_USER')
        password = os.environ.get('DB_PASS')
        
        logging.info(f'DB Config - Server: {server}, Database: {database}, User: {username}')
        
        connection_string = (
            f'DRIVER={{ODBC Driver 18 for SQL Server}};'
            f'SERVER={server};'
            f'DATABASE={database};'
            f'UID={username};'
            f'PWD={password}'
        )
        
        logging.info('Connecting to database...')
        return pyodbc.connect(connection_string)
    except Exception as e:
        logging.error(f'Database connection error: {str(e)}')
        raise
