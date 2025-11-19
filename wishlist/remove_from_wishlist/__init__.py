import logging
import json
import os
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Remove from wishlist function triggered')
    
    try:
        req_body = req.get_json()
        user_id = req_body.get('user_id')
        watch_id = req_body.get('watch_id')
        
        if not user_id or not watch_id:
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "user_id and watch_id required"
                }),
                status_code=400,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        logging.info(f'Removing watch {watch_id} from wishlist for user {user_id}')
        
        # Connect to database
        try:
            conn = get_db_connection()
            logging.info('Database connection established')
        except Exception as e:
            logging.error(f'Database connection failed: {str(e)}')
            return func.HttpResponse(
                json.dumps({"success": False, "error": f"Database connection error: {str(e)}"}),
                status_code=500,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        cursor = conn.cursor()
        
        # Delete from wishlist
        cursor.execute("""
            DELETE FROM wishlist 
            WHERE user_id = ? AND watch_id = ?
        """, (user_id, watch_id))
        
        rows_affected = cursor.rowcount
        conn.commit()
        conn.close()
        
        if rows_affected == 0:
            logging.warning(f'No wishlist item found for user {user_id} and watch {watch_id}')
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "Item not found in wishlist"
                }),
                status_code=404,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        logging.info(f'Successfully removed watch {watch_id} from wishlist for user {user_id}')
        
        return func.HttpResponse(
            json.dumps({
                "success": True,
                "message": "Item removed from wishlist",
                "user_id": user_id,
                "watch_id": watch_id
            }),
            status_code=200,
            mimetype="application/json",
            headers={
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "POST, OPTIONS",
                "Access-Control-Allow-Headers": "Content-Type"
            }
        )
        
    except ValueError:
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": "Invalid JSON body"
            }),
            status_code=400,
            mimetype="application/json",
            headers={
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "POST, OPTIONS",
                "Access-Control-Allow-Headers": "Content-Type"
            }
        )
    except Exception as e:
        logging.error(f'Error: {str(e)}')
        import traceback
        logging.error(traceback.format_exc())
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": str(e)
            }),
            status_code=500,
            mimetype="application/json",
            headers={
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "POST, OPTIONS",
                "Access-Control-Allow-Headers": "Content-Type"
            }
        )

def get_db_connection():
    """Create database connection using pyodbc"""
    import pyodbc
    
    server = os.environ.get('DB_SERVER', '')
    database = os.environ.get('DB_NAME', '')
    username = os.environ.get('DB_USER', '')
    password = os.environ.get('DB_PASS', '')
    
    # Azure SQL connection string format
    server_clean = server.replace('tcp:', '').replace(',1433', '').strip()
    
    logging.info(f'Connecting to server: {server_clean}')
    
    # Use ODBC Driver 18 with TrustServerCertificate=yes for Azure SQL
    connection_string = (
        'DRIVER={ODBC Driver 18 for SQL Server};'
        f'SERVER={server_clean};'
        f'DATABASE={database};'
        f'UID={username};'
        f'PWD={password};'
        'Encrypt=yes;'
        'TrustServerCertificate=yes;'
        'Connection Timeout=30;'
    )
    
    return pyodbc.connect(connection_string)
