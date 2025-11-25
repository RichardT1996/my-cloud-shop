import logging
import json
import os
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Add to wishlist function triggered')
    
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
        
        logging.info(f'Adding watch {watch_id} to wishlist for user {user_id}')
        
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
        
        # Check if already in wishlist
        cursor.execute("""
            SELECT id FROM wishlist 
            WHERE user_id = %s AND watch_id = %s
        """, (user_id, watch_id))
        
        existing = cursor.fetchone()
        
        if existing:
            conn.close()
            logging.info(f'Watch {watch_id} already in wishlist for user {user_id}')
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "Item already in wishlist"
                }),
                status_code=409,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        # Insert into wishlist
        cursor.execute("""
            INSERT INTO wishlist (user_id, watch_id, added_at)
            VALUES (%s, %s, NOW())
        """, (user_id, watch_id))
        
        conn.commit()
        conn.close()
        
        logging.info(f'Successfully added watch {watch_id} to wishlist for user {user_id}')
        
        return func.HttpResponse(
            json.dumps({
                "success": True,
                "message": "Item added to wishlist",
                "user_id": user_id,
                "watch_id": watch_id
            }),
            status_code=201,
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
