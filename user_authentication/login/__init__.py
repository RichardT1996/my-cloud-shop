import logging
import json
import os
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Login function triggered')
    
    try:
        # Try to import pymssql
        try:
            import pymssql
            logging.info('pymssql imported successfully')
        except Exception as e:
            logging.error(f'Failed to import pymssql: {str(e)}')
            return func.HttpResponse(
                json.dumps({"success": False, "error": f"Database driver error: {str(e)}"}),
                status_code=500,
                mimetype="application/json"
            )
        
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
        return func.HttpResponse(
            json.dumps({"success": False, "error": "Internal server error"}),
            status_code=500,
            mimetype="application/json"
        )

def get_db_connection():
    """Create database connection"""
    import pymssql
    
    server = os.environ.get('DB_SERVER')
    database = os.environ.get('DB_NAME')
    username = os.environ.get('DB_USER')
    password = os.environ.get('DB_PASS')
    
    # Remove 'tcp:' prefix and ',1433' suffix from server string
    server_clean = server.replace('tcp:', '').replace(',1433', '')
    
    logging.info(f'Connecting to: {server_clean}, database: {database}, user: {username}')
    
    return pymssql.connect(
        server=server_clean,
        user=username,
        password=password,
        database=database
    )
