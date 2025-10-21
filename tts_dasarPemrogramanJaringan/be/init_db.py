from app import create_app, db
from app.models.tokobuku import TokoBuku
from datetime import date, datetime
import sys

def init_database():
    app = create_app()

    with app.app_context():
        try:
            print("Droppping existing tables...")
            db.drop_all()

            print("Creating database tables...")
            db.create_all()

            print("Database tables created successfully!")
            return True
        except Exception as e:
            print(f"Error creating database: {e}")
            return False

def seed_sample_data():
    app = create_app()

    with app.app_context():
        try:
            if TokoBuku.query.first():
                print("Sample data already exists. Skipping seed operations")
                return True
            
            sample_databases = [
                {
                    'title': 'The Great Gatsby',
                    'author': 'F. Scott Fitzgerald',
                    'published_date': date(1925, 4, 10),
                    'genre': 'Fiction',
                    'price': 10.99,
                    'stock': 30,
                    'description': 'A novel set in the Roaring Twenties.'
                },
                {
                    'title': 'To Kill a Mockingbird',
                    'author': 'Harper Lee',
                    'published_date': date(1960, 7, 11),
                    'genre': 'Fiction',
                    'price': 7.99,
                    'stock': 50,
                    'description': 'A novel about racial injustice in the Deep South.'
                },
                {
                    'title': '1984',
                    'author': 'George Orwell',
                    'published_date': date(1949, 6, 8),
                    'genre': 'Dystopian',
                    'price': 8.99,
                    'stock': 40,
                    'description': 'A novel depicting a totalitarian society.'
                },
                {
                    'title': 'A Brief History of Time',
                    'author': 'Stephen Hawking',
                    'published_date': date(1988, 3, 1),
                    'genre': 'Science',
                    'price': 15.99,
                    'stock': 20,
                    'description': 'An overview of cosmology for the general public.'
                },
                {
                    'title': 'The Art of War',
                    'author': 'Sun Tzu',
                    'published_date': None,
                    'genre': 'Philosophy',
                    'price': 5.99,
                    'stock': 60,
                    'description': 'An ancient Chinese military treatise.'
                }
            ]

            print("Adding sample data...")
            for emp_data in sample_databases:
                new_db = TokoBuku(**emp_data)
                db.session.add(new_db)

            db.session.commit()
            print(f"Successfully added {len(sample_databases)} sample records.")
            return True
        except Exception as e:
            print(f"Error seeding sample data: {e}")
            db.session.rollback()
            return False

def main():
    print("Toko Buku Database Initialization Script")
    print("=" * 50)

    if not init_database():
        print("Database initialization failed. Exiting.")
        sys.exit(1)

    while True:
        response = input("Do you want to seed the database with sample data? (y/n): ").strip().lower()
        if response in ['y', 'yes']:
            seed_sample_data()
            break
        elif response in ['n', 'no']:
            print("Skipping sample data seeding.")
            break
        else:
            print("Invalid input. Please enter 'y' or 'n'.")
    
    print("\nDatabase initialization process completed.")
    print("\nYou can now run the application using:")
    print("python app.py")

if __name__ == '__main__':
    main()
