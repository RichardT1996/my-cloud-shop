import logging
import json
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
                mimetype="application/json"
            )
        
        logging.info(f'Adding watch {watch_id} to wishlist for user {user_id}')
        
        # For now, return a simple response
        # In production, this would insert into the database
        return func.HttpResponse(
            json.dumps({
                "success": True,
                "message": "Item added to wishlist",
                "user_id": user_id,
                "watch_id": watch_id,
                "note": "Connect to database to actually add item"
            }),
            status_code=201,
            mimetype="application/json"
        )
        
    except ValueError:
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": "Invalid JSON body"
            }),
            status_code=400,
            mimetype="application/json"
        )
    except Exception as e:
        logging.error(f'Error: {str(e)}')
        return func.HttpResponse(
            json.dumps({
                "success": False,
                "error": str(e)
            }),
            status_code=500,
            mimetype="application/json"
        )
