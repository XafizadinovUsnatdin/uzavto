import json
import os
import csv

# To‘g‘ri yo‘llar
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
FORECAST_DIR = os.path.join(BASE_DIR, 'future_sales_monthly')
CSV_FILE = os.path.join(BASE_DIR, 'data','daily_car_sales.csv')
OUTPUT_FILE = os.path.join(FORECAST_DIR, 'monthly_summary.json')

# CSVdan product_id -> model mappingni olish
def load_product_models(csv_file):
    mapping = {}
    with open(csv_file, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            try:
                product_id = int(row['product_id'])
                model = row['model'].strip()
                if product_id not in mapping:
                    mapping[product_id] = model
            except Exception as e:
                print(f"⚠️ CSV qatorni o‘qishda xato: {e}")
    return mapping

# Asosiy funksiya
def display_monthly_sales():
    months = ['2025-01', '2025-02', '2025-03']
    results = {}
    id_to_model = load_product_models(CSV_FILE)

    for month in months:
        file_path = os.path.join(FORECAST_DIR, f'forecast_{month}.json')
        print(f"✅ Tekshirilmoqda: {file_path}")

        if not os.path.exists(file_path):
            print(f"❌ JSON fayl topilmadi: {file_path}")
            continue

        with open(file_path, 'r') as f:
            forecast_data = json.load(f)

        monthly_result = {}
        for product_id_str, data in forecast_data.items():
            try:
                product_id = int(product_id_str)
                model_name = id_to_model.get(product_id)
                if not model_name:
                    print(f"⚠️ Model topilmadi: product_id = {product_id}")
                    continue
                total = sum(item['predicted_quantity'] for item in data['forecast'])
                monthly_result[model_name] = int(round(total))
                print(f" {model_name}: {int(round(total))} dona")
            except Exception as e:
                print(f"⚠️ Xatolik: {e}")

        results[month] = monthly_result
        print('-' * 40)

    # JSON formatda saqlash
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=4, ensure_ascii=False)

    print(f"\n✅ Natijalar saqlandi: {OUTPUT_FILE}")

if __name__ == '__main__':
    display_monthly_sales()
