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
                   wt.name, wt.brand, wt.price, wt.image_url
            FROM wishlist w
            INNER JOIN watches wt ON w.watch_id = wt.id
            WHERE w.user_id = %s
            ORDER BY w.added_at DESC
        """, (user_id,))
        
        items = []
        for row in cursor.fetchall():
            items.append({
                "id": row['id'],
                "user_id": row['user_id'],
                "watch_id": row['watch_id'],
                "added_at": row['added_at'].isoformat() if row['added_at'] else None,
                "name": row['name'],
                "brand": row['brand'],
                "price": float(row['price']) if row['price'] else 0,
                "image_url": row['image_url']
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
