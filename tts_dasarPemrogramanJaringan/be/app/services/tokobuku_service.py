from app import db
from app.models.tokobuku import TokoBuku
from sqlalchemy.exc import IntegrityError
from datetime import datetime

class TokoBukuService:

    @staticmethod
    def get_all_bukus(page=1, per_page=10):
        try:
            bukus = TokoBuku.query.paginate(page=page, per_page=per_page, error_out=False)
            return {
                'bukus': [buku.to_dict() for buku in bukus.items],
                'total': bukus.total,
                'pages': bukus.pages,
                'current_page': bukus.page,
                'per_page': bukus.per_page
            }
        except Exception as e:
            raise Exception(f"Error retrieving bukus: {str(e)}")

    @staticmethod
    def get_buku_by_id(buku_id):
        try:
            buku = TokoBuku.query.filter_by(id=buku_id).first()
            if not buku:
                return None
            return buku.to_dict()
        except Exception as e:
            raise Exception(f"Error retrieving buku: {str(e)}")

    @staticmethod
    def create_buku(buku_data):
        try:
            existing_buku = TokoBuku.query.filter_by(title=buku_data['title'].strip().lower()).first()
            if existing_buku:
                raise ValueError("Buku with this title already exists")
            
            buku = TokoBuku(**buku_data)
            db.session.add(buku)
            db.session.commit()
            return buku.to_dict()
        except IntegrityError:
            db.session.rollback()
            raise ValueError("Buku with this title already exists")
        except Exception as e:
            db.session.rollback()
            raise Exception(f"Error creating buku: {str(e)}")

    @staticmethod
    def update_buku(buku_id, buku_data):
        try:
            buku = TokoBuku.query.filter_by(id=buku_id).first()
            if not buku:
                return None

            if 'title' in buku_data:
                existing_buku = TokoBuku.query.filter(
                    TokoBuku.title == buku_data['title'].strip().lower(),
                    TokoBuku.id != buku_id
                ).first()
                if existing_buku:
                    raise ValueError("Another buku with this title already exists")
            for key, value in buku_data.items():
                if hasattr(buku, key):
                    setattr(buku, key, value)
                
            buku.updated_at = datetime.utcnow()
            db.session.commit()
            return buku.to_dict()
        except IntegrityError:
            db.session.rollback()
            raise ValueError("Another buku with this title already exists")
        except Exception as e:
            db.session.rollback()
            raise Exception(f"Error updating buku: {str(e)}")
    
    @staticmethod
    def delete_buku(buku_id):
        try:
            buku = TokoBuku.query.filter_by(id=buku_id).first()
            if not buku:
                return None
            
            db.session.delete(buku)
            db.session.commit()
            return True
        except Exception as e:
            db.session.rollback()
            raise Exception(f"Error deleting buku: {str(e)}")
    
    @staticmethod
    def search_bukus(query, page=1, per_page=10):
        try:
            search_filter = db.or_(
                TokoBuku.title.ilike(f"%{query}%"),
                TokoBuku.author.ilike(f"%{query}%"),
                TokoBuku.genre.ilike(f"%{query}%")
            )

            bukus = TokoBuku.query.filter(search_filter).paginate(page=page, per_page=per_page, error_out=False)

            return {
                'bukus': [buku.to_dict() for buku in bukus.items],
                'total': bukus.total,
                'pages': bukus.pages,
                'current_page': bukus.page,
                'per_page': bukus.per_page
            }
        except Exception as e:
            raise Exception(f"Error searching bukus: {str(e)}")