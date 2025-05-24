import json
import os

# To‘g‘ri direktoriya yo‘li
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
FORECAST_DIR = os.path.join(BASE_DIR, 'future_sales_monthly')
OUTPUT_DIR = os.path.join(BASE_DIR, 'future_sales_monthly')  # Natija shu yerga saqlanadi
os.makedirs(OUTPUT_DIR, exist_ok=True)  # Agar 'data' papkasi mavjud bo'lmasa, yaratadi

def display_monthly_sales():
    months = ['2025-01', '2025-02', '2025-03']
    results = {}  # Yakuniy natijalar shu yerda to‘planadi

    for month in months:
        json_path = os.path.join(FORECAST_DIR, f'forecast_{month}.json')
        print(f"Checking file: {json_path}")  # Debug uchun

        if not os.path.exists(json_path):
            print(f"❌ JSON fayl topilmadi: {json_path}")
            continue

        try:
            with open(json_path, 'r') as f:
                forecast_data = json.load(f)
        except Exception as e:
            print(f"❌ JSON faylni o'qishda xato ({month}): {e}")
            continue

        monthly_result = {}
        for product_id, data in forecast_data.items():
            monthly_total = sum(item['predicted_quantity'] for item in data['forecast'])
            monthly_result[product_id] = int(round(monthly_total))
            print(f"Mahsulot ID: {product_id}, Bashorat qilingan umumiy sotuv: {int(round(monthly_total))} dona")

        results[month] = monthly_result
        print("-" * 50)

    # Natijalarni 'data/monthly_summary.json' faylga saqlash
    output_path = os.path.join(OUTPUT_DIR, 'monthly_summary.json')
    with open(output_path, 'w') as out_file:
        json.dump(results, out_file, indent=4)

    print(f"\n✅ Natijalar saqlandi: {output_path}")

if __name__ == "__main__":
    display_monthly_sales()
