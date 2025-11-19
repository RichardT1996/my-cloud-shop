import logging
import json
import os
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Get wishlist function triggered')
    
    try:
        # Get user_id from query parameters or JSON body
        user_id = req.params.get('user_id')
        
        if not user_id:
            try:
                req_body = req.get_json()
                user_id = req_body.get('user_id')
            except ValueError:
                pass
        
        if not user_id:
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "user_id required"
                }),
                status_code=400,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "GET, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        logging.info(f'Retrieving wishlist for user: {user_id}')
        
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
                    "Access-Control-Allow-Methods": "GET, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        cursor = conn.cursor()
        
        # Query wishlist with watch details
        cursor.execute("""
            SELECT w.id, w.user_id, w.watch_id, w.added_at,
                   wt.name, wt.brand, wt.price, wt.image
            FROM wishlist w
            INNER JOIN watches wt ON w.watch_id = wt.id
            WHERE w.user_id = ?
            ORDER BY w.added_at DESC
        """, (user_id,))
        
        items = []
        for row in cursor.fetchall():
            items.append({
                "id": row[0],
                "user_id": row[1],
                "watch_id": row[2],
                "added_at": row[3].isoformat() if row[3] else None,
                "name": row[4],
                "brand": row[5],
                "price": float(row[6]) if row[6] else 0,
                "image": row[7]
            })
        
        conn.close()
        
        logging.info(f'Retrieved {len(items)} wishlist items for user {user_id}')
        
        return func.HttpResponse(
            json.dumps({
                "success": True,
                "user_id": user_id,
                "items": items
            }),
            status_code=200,
            mimetype="application/json",
            headers={
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods": "GET, OPTIONS",
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
                "Access-Control-Allow-Methods": "GET, OPTIONS",
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
