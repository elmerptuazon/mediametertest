# csv_processor.py

import csv
import json
import threading
from redis_queue import RedisQueue
from mongodb_client import MongoDBClient

class CSVProcessor:    
    def __init__(self):
        self.mongo_client = MongoDBClient()
        self.redis_queue = RedisQueue()

    def process_csv(self, file_paths):
        """Processes one or multiple CSV files, publishing each row to Redis."""
        for file_path in file_paths:
            try:
                with open(file_path, 'r') as csvfile:
                    reader = csv.DictReader(csvfile)
                    for row in reader:
                        self.redis_queue.publish(json.dumps(row))
            except FileNotFoundError:
                print(f"Error: File {file_path} not found.")
            except Exception as e:
                print(f"Error processing file {file_path}: {e}")

    def consume_queue(self):
        """Continuously consumes messages from the Redis queue and inserts them into MongoDB."""
        for message in self.redis_queue.consume():
            record = json.loads(message)
            self.mongo_client.insert_record(record)

    def run(self):
        """Starts the queue consumer in a separate thread."""
        consumer_thread = threading.Thread(target=self.consume_queue)
        consumer_thread.daemon = True
        consumer_thread.start()

if __name__ == '__main__':
    processor = CSVProcessor()
    processor.run()

    csv_files = ['medallists.csv']
    processor.process_csv(csv_files)
