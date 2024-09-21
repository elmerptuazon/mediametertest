# redis_queue.py

import redis

class RedisQueue:    
    def __init__(self, queue_name='csv_queue', host='localhost', port=6379):
        self.redis = redis.Redis(host=host, port=port)
        self.queue_name = queue_name

    def publish(self, message):
        self.redis.lpush(self.queue_name, message)

    def consume(self):
        while True:
            message = self.redis.brpop(self.queue_name)[1]
            yield message
