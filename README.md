## Turin x IT Park: AI Hackathon 2025 
# Maqsad:
Avtosalonga tashrif buyurgan mijozlarning yoshi, jinsi va salonda qancha vaqt bo‘lganligi asosida statistik tahlil qilish, ularni tahlil qiluvchi va vizual tarzda ko‘rsatadigan tizim yaratish. 

# Vazifalar:
Ma’lumotlar modeli tuzish
Mijozlar va ularning tashriflari uchun quyidagicha ma’lumotlar bazasi tuzing:
Mijozlar jadvali:
ID
Ism
Yosh
Jins
Telefon raqami
Tashriflar jadvali:
Tashrif ID
Mijoz ID
Kirish vaqti
Chiqish vaqti
Maqsadi (yangi mashina ko‘rish, servis, test drive, hujjatlar, va h.k.)


Bu loyiha — avtosalonga tashrif buyuruvchi mijozlar, mavjud avtomobillar va kundalik sotuvlar asosida statistik tahlil va prognoz qilish imkonini beruvchi tizimdir. Loyihada foydalanuvchi uchun tushunarli vizualizatsiyalar, tahlillar va kelajak sotuvlarini bashorat qilish imkoniyati mavjud.

- haftalik/oylik sotuv dinamikasi,
- kelajakdagi mashina sotuvlarini prognozlash
## 🗃️ Ma'lumotlar

### 1. `mijozlar.csv`
| id | name   | age | gender | phone_number |
|----|--------|-----|--------|---------------|
| 1  | Usnatdin | 21  | erkak   | 998882202440  |

### 2. `tashriflar.csv`
| visit_id | customer_id | entry_time        | exit_time         | purpose        |
|----------|--------------|------------------|-------------------|----------------|
| 1        | 1            | 1/1/2022 14:00    | 1/1/2022 15:34    | Hujjatlar      |

### 3. `sales.csv`
| id | date      | product_id | model     | color      | variant | quantity |
|----|-----------|------------|-----------|------------|---------|----------|
| 1  | 1/1/2024  | 1          | Cobalt    | Oq         | LT      | 8        |

### 4. `cars.csv`
| car_id | brand    | model     | year | price | category  | engine_type | stock_quantity |
|--------|----------|-----------|------|-------|-----------|--------------|----------------|
| 1      | Toyota   | Corolla   | 2022 | 25000 | Sedan     | benzin       | 10             |

## 📈 Imkoniyatlar

- Mijoz tashrif tahlili: o‘rtacha tashrif davomiyligi, maqsadlar statistikasi
- Avtomobil bozor tahlili: eng ko‘p sotilayotgan brend/model
- Sotuv tendensiyasi grafigi
- Kelajakdagi sotuvlar prognozi (Time Series Model)
- Vizualizatsiyalar: Matplotlib, Seaborn, Plotly orqali

## 🛠 Texnologiyalar

- Python (Pandas, Numpy)
- Matplotlib, Seaborn
- Jupyter Notebook
- [Kelajakda qo‘shilishi mumkin]: Flask yoki Streamlit asosidagi web dashboard 

## 📂 Loyihani ishga tushurish

1. Repository’ni klonlang:
   ```bash
   git clone https://github.com/username/avtosalon-analytics.git
   cd avtosalon-analytics
Virtual muhit yarating va kutubxonalarni o‘rnating:
python -m venv venv
source venv/bin/activate
pip install -r requirements.txt

📌 Eslatma
Ma’lumotlar sun’iy ravishda yaratilgan va faqat o‘quv/test maqsadida ishlatiladi.

👨‍💻 Jamoa:
TAHLILCHI
