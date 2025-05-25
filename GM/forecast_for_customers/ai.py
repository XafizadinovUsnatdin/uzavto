import pandas as pd
import numpy as np
import json
from datetime import datetime, timedelta
from keras.models import Sequential
from keras.layers import Input, LSTM, Dense


class AutoForecastSystem:
    def __init__(self, visits_csv, window_size=24):
        self.window_size = window_size
        self.visits_csv = visits_csv

        # Boshlang'ich soatlik tashriflarni o'qish
        self.data, self.start_date = self._load_hourly_visits()

        self.model = self._build_model()
        self._train_model()

    def _load_hourly_visits(self):
        df = pd.read_csv(self.visits_csv, parse_dates=['entry_time', 'exit_time'])
        # Soatlik tashriflarni hisoblash
        visits = []

        # Boshlang‘ich sana (eng kichik sana entry_time dan)
        min_date = df['entry_time'].min().floor('D')

        # Har bir tashrif uchun har bir soatni hisoblash
        hourly_counts = {}
        for _, row in df.iterrows():
            start = row['entry_time'].floor('h')
            end = row['exit_time'].ceil('h') - timedelta(hours=1)
            current = start
            while current <= end:
                hourly_counts[current] = hourly_counts.get(current, 0) + 1
                current += timedelta(hours=1)

        # Sana-soatni ketma-ket ro'yxatga olish
        max_date = df['exit_time'].max().ceil('h')
        total_hours = int((max_date - min_date).total_seconds() // 3600) + 1

        data = []
        for i in range(total_hours):
            hour = min_date + timedelta(hours=i)
            data.append(hourly_counts.get(hour, 0))

        return data, min_date

    def _build_model(self):
        model = Sequential()
        model.add(Input(shape=(self.window_size, 1)))
        model.add(LSTM(32))
        model.add(Dense(1))
        model.compile(optimizer='adam', loss='mse')
        return model

    def _prepare_xy(self):
        X, y = [], []
        for i in range(len(self.data) - self.window_size):
            X.append(self.data[i:i + self.window_size])
            y.append(self.data[i + self.window_size])
        X = np.array(X).reshape(-1, self.window_size, 1)
        y = np.array(y)
        return X, y

    def _train_model(self):
        if len(self.data) > self.window_size:
            X, y = self._prepare_xy()
            self.model.fit(X, y, epochs=50, verbose=0)

    def add_new_visits(self, new_visits_df):
        """
        Yangi tashriflar DataFrame (entry_time, exit_time)
        ko'rinishida kiritiladi.
        Ma'lumotlar qo'shiladi, model yangilanadi va bashorat qaytariladi.
        """
        # Yangi tashriflar soatlik hisoblash va qo'shish
        hourly_counts = {}
        for _, row in new_visits_df.iterrows():
            start = row['entry_time'].floor('h')
            end = row['exit_time'].ceil('h') - timedelta(hours=1)
            current = start
            while current <= end:
                hourly_counts[current] = hourly_counts.get(current, 0) + 1
                current += timedelta(hours=1)

        # Mavjud ma'lumotlar oxiri qaysi soat ekan
        last_hour = self.start_date + timedelta(hours=len(self.data) - 1)

        # Yangi soatlarni hisoblash, agar ular mavjuddan keyin bo'lsa, qo'shamiz
        current_hour = last_hour + timedelta(hours=1)
        while current_hour <= max(hourly_counts.keys()):
            count = hourly_counts.get(current_hour, 0)
            self.data.append(count)
            current_hour += timedelta(hours=1)

        self._train_model()
        return self.predict_next_week()

    def predict_next_week(self):
        """
        Kelgusi 7 kun, har kuni 24 soat uchun bashorat qilish va
        JSON formatida dictionary qaytarish
        """
        predictions = {}
        input_seq = np.array(self.data[-self.window_size:]).reshape(1, self.window_size, 1)
        now = self.start_date + timedelta(hours=len(self.data) - 1)

        # 7 kun, har kuni 24 soat = 168 soat
        total_hours = 7 * 24

        for i in range(total_hours):
            pred = self.model.predict(input_seq, verbose=0)[0][0]
            pred_rounded = max(round(pred), 0)

            current_hour = now + timedelta(hours=i + 1)
            day_str = current_hour.strftime('%Y-%m-%d')
            hour_str = f"{current_hour.hour:02d}:00–{(current_hour.hour + 1) % 24:02d}:00"

            if day_str not in predictions:
                predictions[day_str] = {}
            predictions[day_str][hour_str] = pred_rounded

            # Update input sequence for next prediction
            input_seq = np.append(input_seq[:, 1:, :], [[[pred]]], axis=1)

        # JSON faylga saqlash
        with open('weekly_forecast/weekly_forecast.json', 'w', encoding='utf-8') as f:
            json.dump(predictions, f, ensure_ascii=False, indent=2)

        return predictions


# --- Misol uchun ishlatish ---

# Boshlang'ich fayldan o'qish
initial_csv = 'tashriflar.csv'  # Fayl nomi o'zgartiring o'z faylingizga

system = AutoForecastSystem(initial_csv)

# Yangi tashriflar misoli (DataFrame ko'rinishida)
new_visits_data = {
    'visit_id': [201, 202],
    'customer_id': [1, 2],
    'entry_time': [pd.Timestamp('2025-05-25 10:15:00'), pd.Timestamp('2025-05-25 11:40:00')],
    'exit_time': [pd.Timestamp('2025-05-25 11:00:00'), pd.Timestamp('2025-05-25 12:30:00')],
    'purpose': ['test drive', 'xizmat']
}
new_visits_df = pd.DataFrame(new_visits_data)

# Yangi ma'lumotlar qo'shish, modelni yangilash va kelgusi 7 kunlik bashorat olish
weekly_forecast = system.add_new_visits(new_visits_df)

print("Kelgusi 7 kunlik soatlik bashorat:")
print(json.dumps(weekly_forecast, indent=2, ensure_ascii=False))
