# LARAVEL (api/test)

## Setup
- composer install
- php artisan storage:link
- php artisan serve

## Note
- I store it as json not in database since there is no database connection mentioned in the instruction
- To see validation response from api add in headers Accept: application/json

## Route
- /api/upload
- /api/aggregated_stats/event

# PYTHON (service/python)

## Setup
- pip install -r requirements.txt
- Setup local redis and mongodb

## Usage
- run python csv_processor.py
- See mongodb and redis for data stored
