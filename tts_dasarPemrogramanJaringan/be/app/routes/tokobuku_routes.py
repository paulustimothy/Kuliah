from flask import Blueprint, request, jsonify
from marshmallow import ValidationError
from app.models.tokobuku import tokobuku_schema, tokobukus_schema
from app.services.tokobuku_service import TokoBukuService

tokobuku_bp = Blueprint('tokobuku', __name__)

@tokobuku_bp.route('/books', methods=['GET'])
def get_tokobukus():
    try:
        page = request.args.get('page', 1, type=int)
        per_page = min(request.args.get('per_page', 10, type=int), 100)
        search_query = request.args.get('search', '').strip()

        if search_query:
            result = TokoBukuService.search_bukus(search_query, page, per_page)
        else:
            result = TokoBukuService.get_all_bukus(page, per_page)

        return jsonify({
            'success': True,
            'data': result,
            'message': 'Tokobukus retrieved successfully'
        }), 200

    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e),
            'message': 'Failed to retrieve tokobukus'
        }), 500

@tokobuku_bp.route('/book/<int:book_id>', methods=['GET'])
def get_tokobuku(book_id):
    try:
        tokobuku = TokoBukuService.get_buku_by_id(book_id)

        if not tokobuku:
            return jsonify({
                'success': False,
                'message': 'Tokobuku not found'
            }), 404

        return jsonify({
            'success': True,
            'data': tokobuku,
            'message': 'Tokobuku retrieved successfully'
        }), 200

    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e),
            'message': 'Failed to retrieve tokobuku'
        }), 500

@tokobuku_bp.route('/book', methods=['POST'])
def create_tokobuku():
    try:
        try:
            buku_data = tokobuku_schema.load(request.json)
        except ValidationError as err:
            return jsonify({
                'success': False,
                'errors': err.messages,
                'message': 'Invalid input data'
            }), 400

        new_buku = TokoBukuService.create_buku(buku_data)

        return jsonify({
            'success': True,
            'data': new_buku,
            'message': 'Tokobuku created successfully'
        }), 201

    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e),
            'message': 'Failed to create tokobuku'
        }), 500

    except ValueError as e:
        return jsonify({
            'success': False,
            'error': str(e),
            'message': 'Invalid data provided'
        }), 400

@tokobuku_bp.route('/book/<int:book_id>', methods=['PUT'])
def update_tokobuku(book_id):
    try:
        try:
            buku_data = tokobuku_schema.load(request.json, partial=True)
        except ValidationError as err:
            return jsonify({
                'success': False,
                'errors': err.messages,
                'message': 'Invalid input data'
            }), 400

        updated_buku = TokoBukuService.update_buku(book_id, buku_data)

        if not updated_buku:
            return jsonify({
                'success': False,
                'message': 'Tokobuku not found'
            }), 404

        return jsonify({
            'success': True,
            'data': updated_buku,
            'message': 'Tokobuku updated successfully'
        }), 200

    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e),
            'message': 'Failed to update tokobuku'
        }), 500

    except ValueError as e:
        return jsonify({
            'success': False,
            'error': str(e),
            'message': 'Invalid data provided'
        }), 400

@tokobuku_bp.route('/book/<int:book_id>', methods=['DELETE'])
def delete_tokobuku(book_id):
    try:
        deleted = TokoBukuService.delete_buku(book_id)

        if not deleted:
            return jsonify({
                'success': False,
                'message': 'Tokobuku not found'
            }), 404

        return jsonify({
            'success': True,
            'message': 'Tokobuku deleted successfully'
        }), 200

    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e),
            'message': 'Failed to delete tokobuku'
        }), 500

@tokobuku_bp.route('/health', methods=['GET'])
def health_check():
    return jsonify({
        'success': True,
        'message': 'Tokobuku service is healthy'
    }), 200