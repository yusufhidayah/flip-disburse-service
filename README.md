# flip-disburse-service
https://gist.github.com/luqmansungkar/0fe5d390742f8ad2e4d1522c1029fbae

### How to run this code

#### 1. Set environment file
```
# copy env.php.example to env.php
cp env.php.example env.php
# edit or change necessary fields
vim env.php
```

#### 2. Run database migration file
```
# run migration file to create database
# will create 3 tables: transactions table, flip_disbursements table & flip_response_logs table
php migration.php
```

#### 3. Create Disbursement
```
# to create disbursement
# php disburse.php create {amount} {bank_code} {account_number}
php disburse.php create 35000 bni 1300004453222
```

#### 4. Get Disbursement status
```
# get disbursement status
# php disburse.php status (disbursment_id)
# {disbursement_id} is id from flip_disbursements table
php disburse.php status 2
```

### Database
There are 3 tables:
1. transactions table holds amount of transaction.
2. flip_disbursements table holds disbursement data and transaction id.
3. flip_response_logs table contain body response from API when request (POST & GET) disbursement.