import logging
import json
import os
import base64
import uuid
from datetime import datetime
import azure.functions as func

def main(req: func.HttpRequest) -> func.HttpResponse:
    logging.info('Upload image function triggered')
    
    try:
        # Get the file from the request
        req_body = req.get_json()
        
        if not req_body or 'image' not in req_body or 'filename' not in req_body:
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": "image and filename required"
                }),
                status_code=400,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        # Get base64 image data and filename
        image_data = req_body.get('image')
        original_filename = req_body.get('filename')
        
        logging.info(f'Uploading image: {original_filename}')
        
        # Generate unique filename
        file_extension = original_filename.split('.')[-1] if '.' in original_filename else 'jpg'
        unique_filename = f"{uuid.uuid4().hex}_{datetime.now().strftime('%Y%m%d%H%M%S')}.{file_extension}"
        
        # Decode base64 image
        try:
            # Remove data URL prefix if present
            if ',' in image_data:
                image_data = image_data.split(',')[1]
            image_bytes = base64.b64decode(image_data)
        except Exception as e:
            logging.error(f'Failed to decode image: {str(e)}')
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": f"Invalid image data: {str(e)}"
                }),
                status_code=400,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
        # Upload to Azure Blob Storage
        try:
            from azure.storage.blob import BlobServiceClient
            
            connection_string = os.environ.get('AZURE_STORAGE_CONNECTION_STRING', '')
            
            if not connection_string:
                raise Exception("Storage connection string not configured")
            
            blob_service_client = BlobServiceClient.from_connection_string(connection_string)
            container_name = "images"
            
            # Get blob client
            blob_client = blob_service_client.get_blob_client(container=container_name, blob=unique_filename)
            
            # Upload the image
            blob_client.upload_blob(image_bytes, overwrite=True)
            
            # Get the URL
            blob_url = blob_client.url
            
            logging.info(f'Image uploaded successfully: {blob_url}')
            
            return func.HttpResponse(
                json.dumps({
                    "success": True,
                    "message": "Image uploaded successfully",
                    "url": blob_url,
                    "filename": unique_filename
                }),
                status_code=200,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
            
        except Exception as e:
            logging.error(f'Storage upload failed: {str(e)}')
            import traceback
            logging.error(traceback.format_exc())
            return func.HttpResponse(
                json.dumps({
                    "success": False,
                    "error": f"Storage upload error: {str(e)}"
                }),
                status_code=500,
                mimetype="application/json",
                headers={
                    "Access-Control-Allow-Origin": "*",
                    "Access-Control-Allow-Methods": "POST, OPTIONS",
                    "Access-Control-Allow-Headers": "Content-Type"
                }
            )
        
    except ValueError as e:
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
