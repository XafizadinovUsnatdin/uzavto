import pandas as pd
from faker import Faker
import random

fake = Faker('uz_UZ')
Faker.seed(0)
random.seed(0)

num_customers = 500
customers = []

genders = ['Erkak', 'Ayol']

for i in range(1, num_customers + 1):
    gender = random.choice(genders)
    name = fake.name_male() if gender == 'Erkak' else fake.name_female()
    age = random.randint(18, 65)
    phone_number = fake.phone_number()

    customers.append({
        'id': i,
        'name': name,
        'age': age,
        'gender': gender,
        'phone_number': phone_number
    })

df_customers = pd.DataFrame(customers)

# CSV faylga saqlash
df_customers.to_csv('customers_mock_data.csv', index=False)

print(df_customers.head())
