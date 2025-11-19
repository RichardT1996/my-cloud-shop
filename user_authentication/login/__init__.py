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
            "SELECT id, name, email, password FROM shopusers WHERE email = ?",
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
                "id": user[0],
                "name": user[1],
                "email": user[2],
                "is_admin": (email == "admin@gmail.com")
            },
            "hashed_password": user[3]
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
    """Create database connection using pyodbc with proper Azure configuration"""
    try:
        import pyodbc
    except ImportError:
        logging.error("pyodbc not available, using struct-based workaround")
        # If pyodbc isn't available, we'll need to install it via Oryx build
        raise Exception("Database driver not available. Please check Oryx build logs.")
    
    server = os.environ.get('DB_SERVER')
    database = os.environ.get('DB_NAME')
    username = os.environ.get('DB_USER')
    password = os.environ.get('DB_PASS')
    
    # Remove 'tcp:' prefix and ',1433' suffix from server string
    server_clean = server.replace('tcp:', '').replace(',1433', '')
    
    logging.info(f'Connecting to: {server_clean}, database: {database}')
    
    # Try ODBC Driver 18 first (newer), then fall back to 17
    drivers = ['ODBC Driver 18 for SQL Server', 'ODBC Driver 17 for SQL Server']
    
    for driver in drivers:
        try:
            connection_string = (
                f'DRIVER={{{driver}}};'
                f'SERVER={server_clean};'
                f'DATABASE={database};'
                f'UID={username};'
                f'PWD={password};'
                f'Encrypt=yes;'
                f'TrustServerCertificate=no;'
                f'Connection Timeout=30;'
            )
            
            logging.info(f'Trying driver: {driver}')
            conn = pyodbc.connect(connection_string)
            logging.info(f'Connected successfully with {driver}')
            return conn
        except Exception as e:
            logging.warning(f'Failed with {driver}: {str(e)}')
            continue
    
    raise Exception("Could not connect with any available ODBC driver")
