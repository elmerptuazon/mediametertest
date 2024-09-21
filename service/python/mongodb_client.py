# mongodb_client.py

from pymongo import MongoClient, errors

class MongoDBClient:    
    def __init__(self, uri='mongodb://localhost:27017/', db_name='csv_data'):
        """Initializes MongoDB client and selects the database and collection."""
        self.client = MongoClient(uri)
        self.db = self.client[db_name]
        self.collection = self.db['medalists']

    def insert_record(self, record):
        try:
            if not self.collection.find_one({'code_athlete': record['code_athlete']}):
                self.collection.insert_one(record)
        except errors.PyMongoError as e:
            print(f"Error inserting record: {e}")

    def cleanup(self):
        self.client.close()
