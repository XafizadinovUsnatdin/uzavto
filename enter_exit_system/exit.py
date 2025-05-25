import os

import cv2
from PIL._tkinter_finder import tk
from pyzbar.pyzbar import decode
from PIL import Image, ImageDraw, ImageFont, ImageTk
import sqlite3
import datetime
from tkinter import messagebox, Tk

def read_qr_from_camera():
    cap = cv2.VideoCapture(2)  # Kamera raqamini moslang

    if not cap.isOpened():
        print("❌ Kamera ochilmadi.")
        return

    found_ids = {}  # IDlar va vaqtni saqlaydi

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        decoded_objs = decode(frame)
        for obj in decoded_objs:
            data = obj.data.decode("utf-8")
            if "ID:" in data:
                for line in data.splitlines():
                    if line.startswith("ID:"):
                        qr_id = line.replace("ID:", "").strip()
                        now = datetime.datetime.now().timestamp()

                        # Har bir QR 5 soniyadan keyin yana o‘qilishi mumkin
                        if qr_id not in found_ids or (now - found_ids[qr_id]) > 5:
                            found_ids[qr_id] = now
                            handle_exit(qr_id)

        cv2.imshow("QR-ni kameraga tuting. ESC - chiqish", frame)

        if cv2.waitKey(1) & 0xFF == 27:  # ESC tugmasi
            break

    cap.release()
    cv2.destroyAllWindows()


import threading

def handle_exit(qr_id):
    conn = sqlite3.connect("autosalon.db")
    cursor = conn.cursor()

    cursor.execute("SELECT enter_time, service_type, status FROM clients WHERE id = ?", (qr_id,))
    result = cursor.fetchone()
    conn.close()

    if not result:
        print(f"❌ ID {qr_id} topilmadi.")
        return

    enter_time_str, service_type, status = result
    if status == 'completed':
        print(f"ℹ️ {qr_id} allaqachon qayd etilgan.")
        return

    enter_time = datetime.datetime.strptime(enter_time_str, "%Y-%m-%d %H:%M:%S")
    exit_time = datetime.datetime.now()
    duration = int((exit_time - enter_time).total_seconds() // 60)

    # bazani yangilash
    conn = sqlite3.connect("autosalon.db")
    cursor = conn.cursor()
    cursor.execute('''
        UPDATE clients
        SET exit_time = ?, duration_minutes = ?, status = 'completed'
        WHERE id = ?
    ''', (exit_time.strftime("%Y-%m-%d %H:%M:%S"), duration, qr_id))
    conn.commit()
    conn.close()

    # ❗ show_exit_info fon threadda chaqiriladi
    threading.Thread(target=show_exit_info, args=(service_type, duration)).start()


def show_exit_info(service_type, duration):
    top = tk.Toplevel(root)
    top.title("✅ Chiqish qayd etildi")
    top.geometry("420x500")
    top.configure(bg="white")

    label_text = tk.Label(top, text="Tashrif buyurganingiz uchun rahmat!", font=("Arial", 14, "bold"), bg="white")
    label_text.pack(pady=20)

    # QR emas, check belgisi
    image_path = os.path.join(os.path.dirname(__file__), "images", "check.png")
    img = Image.open(image_path).resize((250, 250))
    img_tk = ImageTk.PhotoImage(img)

    label_image = tk.Label(top, image=img_tk, bg="white")
    label_image.image = img_tk
    label_image.pack(pady=10)

    info_label = tk.Label(top, text=f"Xizmat: {service_type}\nIchkarida: {duration} daqiqa", font=("Arial", 12), bg="white")
    info_label.pack(pady=10)

    top.after(3000, top.destroy)





if __name__ == "__main__":
    root = Tk()
    root.withdraw()
    read_qr_from_camera()
