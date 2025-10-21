from app import db
from datetime import datetime
from marshmallow import Schema, fields

class TokoBuku(db.Model):
    __tablename__ = 'toko_buku'

    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(100), nullable=False)
    author = db.Column(db.String(100), nullable=False)
    published_date = db.Column(db.Date, nullable=True)
    genre = db.Column(db.String(50), nullable=True)
    price = db.Column(db.Numeric(10, 2), nullable=True)
    stock = db.Column(db.Integer, nullable=True)
    description = db.Column(db.Text, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    def __repr__(self):
        return f'<Book {self.title} by {self.author}>'

    def to_dict(self):
        return {
            'id': self.id,
            'title': self.title,
            'author': self.author,
            'published_date': self.published_date.isoformat() if self.published_date else None,
            'genre': self.genre,
            'price': float(self.price) if self.price else None,
            'stock': self.stock,
            'description': self.description,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }

class TokoBukuSchema(Schema):
    id = fields.Int(dump_only=True)
    title = fields.Str(required=True, validate=lambda x: len(x.strip()) > 0)
    author = fields.Str(required=True, validate=lambda x: len(x.strip()) > 0)
    published_date = fields.Date(allow_none=True)
    genre = fields.Str(allow_none=True)
    price = fields.Decimal(allow_none=True, places=2)
    stock = fields.Int(allow_none=True)
    description = fields.Str(allow_none=True)
    created_at = fields.DateTime(dump_only=True)
    updated_at = fields.DateTime(dump_only=True)

tokobuku_schema = TokoBukuSchema()
tokobukus_schema = TokoBukuSchema(many=True)