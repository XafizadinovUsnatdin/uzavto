import csv
import tkinter as tk
from time import sleep
from tkinter import messagebox
from PIL import Image, ImageDraw, ImageFont, ImageTk
import qrcode
import uuid
import datetime
import sqlite3
import os
import threading
import time


conn = sqlite3.connect("autosalon.db")
cursor = conn.cursor()
cursor.execute('''
CREATE TABLE IF NOT EXISTS clients (
    id TEXT PRIMARY KEY,
    service_type TEXT,
    enter_time TEXT,
    exit_time TEXT,
    duration_minutes INTEGER,
    status TEXT
)
''')
conn.commit()

def process_qr(service_type):
    status_label.config(text="⏳ QR yaratilmoqda...", fg="blue")
    time.sleep(2)

    client_id = str(uuid.uuid4())[:8]
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    # QR ma'lumoti
    qr_data = f"ID: {client_id}\nXizmat: {service_type}\nKirish: {timestamp}"
    qr = qrcode.make(qr_data).resize((300, 300))


    width, height = 500, 600
    img = Image.new('RGB', (width, height), color='white')
    draw = ImageDraw.Draw(img)

    # Fontlar
    try:
        font_title = ImageFont.truetype("arial.ttf", 28)
        font_text = ImageFont.truetype("arial.ttf", 24)
        font_note = ImageFont.truetype("arialbd.ttf", 30)  # qalinroq

    except:
        font_title = ImageFont.load_default()
        font_text = ImageFont.load_default()
        font_note = ImageFont.load_default()

    # Matnlarni o‘rtalash uchun yordamchi funksiya
    def draw_centered_text(y, text, font):
        bbox = draw.textbbox((0, 0), text, font=font)
        w = bbox[2] - bbox[0]  # width
        h = bbox[3] - bbox[1]  # height
        draw.text(((width - w) / 2, y), text, font=font, fill="black")

    # Matn joylash
    draw_centered_text(30, "Assalomu alaykum!", font_title)
    draw_centered_text(80, f"Yo‘nalish: {service_type}", font_text)
    draw_centered_text(130, "Kirish vaqti:", font_text)
    draw_centered_text(160, timestamp, font_text)

    # QR joylash
    img.paste(qr, (width // 2 - qr.size[0] // 2, 220))

    draw_centered_text(580, "Diqqat: Iltimos, xizmat tugagach chiqishda QR’ni skanerlating.", font_note)

    # Faylga saqlash
    filename = f"qr_image_{client_id}.png"
    img.save(filename)
    print(f"✅ QR + matnli rasm saqlandi: {filename}")

    # --- Bazaga yozish ---
    local_conn = sqlite3.connect("autosalon.db")
    local_cursor = local_conn.cursor()
    local_cursor.execute('''
        INSERT INTO clients (id, service_type, enter_time, exit_time, duration_minutes, status)
        VALUES (?, ?, ?, NULL, NULL, ?)
    ''', (client_id, service_type, timestamp, 'active'))
    local_conn.commit()
    local_conn.close()

    # --- CSV faylga yozish ---
    csv_filename = "clients_log.csv"
    file_exists = os.path.isfile(csv_filename)
    with open(csv_filename, mode='a', newline='', encoding='utf-8') as csvfile:
        writer = csv.writer(csvfile)
        if not file_exists:
            writer.writerow(["ID", "Xizmat", "Kirish vaqti", "Status"])
        writer.writerow([client_id, service_type, timestamp, "active"])

    # GUI statusni tozalash va alert chiqarish
    root.after(0, lambda: show_qr_on_main_screen(filename))



def show_qr_on_main_screen(filename):
    status_label.config(text="✅ QR yaratildi!", fg="green")

    qr_img = Image.open(filename)
    qr_img = qr_img.resize((400, 500))
    qr_tk = ImageTk.PhotoImage(qr_img)

    qr_label.config(image=qr_tk)
    qr_label.image = qr_tk

def finish_qr(filename):
    status_label.config(text="")

    top = tk.Toplevel(root)
    top.title("✅ QR yaratildi")
    top.geometry("400x500")
    top.configure(bg="white")

    # Matn
    label_text = tk.Label(
        top,
        text="QR-code olganingiz uchun rahmat!",
        font=("Arial", 14, "bold"),
        bg="white"
    )
    label_text.pack(pady=20)

    # Rasm yuklash
    image_path = os.path.join(os.path.dirname(__file__), "images", "check.png")
    img = Image.open(image_path)
    img = img.resize((250, 250))
    img_tk = ImageTk.PhotoImage(img)

    label_image = tk.Label(top, image=img_tk, bg="white")
    label_image.image = img_tk
    label_image.pack(pady=10)

    # Yopish tugmasi (ixtiyoriy qoladi)
    btn_close = tk.Button(top, text="Yopish", width=20, command=top.destroy, bg="#4CAF50", fg="white", font=("Arial", 12))
    btn_close.pack(pady=20)

    top.after(1000, top.destroy)


def start_qr_thread(service_type):
    thread = threading.Thread(target=process_qr, args=(service_type,))
    thread.start()


root = tk.Tk()
root.title("Avtosalon - Mijoz QR")
root.geometry("400x400")
root.configure(bg="#f2f2f2")

title_label = tk.Label(root, text="Xizmat turini tanlang", font=("Arial", 16, "bold"), bg="#f2f2f2")
title_label.pack(pady=20)

btn_frame = tk.Frame(root, bg="#f2f2f2")
btn_frame.pack()

services = ["Avtomabil xarid qilish", "Texnik ko‘rik", "Maslahat olsh"]
for service in services:
    btn = tk.Button(
        btn_frame, text=service, width=20, height=2,
        font=("Arial", 12),
        bg="#4CAF50", fg="white",
        activebackground="#45a049",
        command=lambda s=service: start_qr_thread(s)
    )
    btn.pack(pady=8)

status_label = tk.Label(root, text="", font=("Arial", 12), bg="#f2f2f2")
status_label.pack(pady=20)
qr_label = tk.Label(root, bg="#f2f2f2")
qr_label.pack(pady=10)
root.mainloop()
