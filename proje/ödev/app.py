import tkinter as tk
from tkinter import ttk
import requests
import json

# stats.php'den verileri çek
def fetch_data():
    try:
        response = requests.get('http://localhost/ödev/stats.php')
        data = response.json()
        return data
    except Exception as e:
        print(f"Veri çekme hatası: {e}")
        return None

# Tkinter penceresini oluştur
root = tk.Tk()
root.title("Mağaza Veri Analizi")
root.geometry("800x400")
root.configure(bg="#f4f4f4")

# Başlık
title_label = tk.Label(root, text="Mağaza Veri Analizi", font=("Arial", 16, "bold"), bg="#f4f4f4", fg="#333")
title_label.pack(pady=10)

# Kutu çerçevesi (kutuları yan yana yerleştirmek için)
frame = tk.Frame(root, bg="#f4f4f4")
frame.pack(pady=10)

# Verileri çek
data = fetch_data()

# Kutu oluşturma fonksiyonu
def create_box(parent, title, content, row, col):
    box = tk.Frame(parent, bg="white", bd=1, relief="solid", padx=10, pady=10, width=200, height=100)
    box.grid(row=row, column=col, padx=10, pady=5, sticky="nsew")
    box.grid_propagate(False)  # Kutu boyutunu sabit tut

    # Başlık
    title_label = tk.Label(box, text=title, font=("Arial", 12, "bold"), bg="white", fg="#333")
    title_label.pack()

    # İçerik
    content_label = tk.Label(box, text=content, font=("Arial", 10), bg="white", fg="#666", wraplength=180, justify="center")
    content_label.pack()

# Verileri kutulara yerleştir
if data:
    # En Çok Satılan Ürün
    top_sold_text = "\n".join([f"{item['name']} - {item['total_sold']} adet" for item in data['top_sold']])
    create_box(frame, "En Çok Satılan Ürün", top_sold_text, 0, 0)

    # En Az Satılan Ürün
    least_sold_text = "\n".join([f"{item['name']} - {item['total_sold']} adet" for item in data['least_sold']])
    create_box(frame, "En Az Satılan Ürün", least_sold_text, 0, 1)

    # En Çok Görüntülenen Ürün
    top_viewed_text = "\n".join([f"{item['name']} - {item['view_count']} görüntülenme" for item in data['top_viewed']])
    create_box(frame, "En Çok Görüntülenen Ürün", top_viewed_text, 0, 2)

    # En Çok Tercih Edilen Kategori
    top_category_text = f"{data['top_category']['name']} - {data['top_category']['total_sold']} adet"
    create_box(frame, "En Çok Tercih Edilen Kategori", top_category_text, 1, 0)

    # Günlük Ciro
    daily_revenue_text = f"{data['daily_revenue'] if data['daily_revenue'] else 0}₺"
    create_box(frame, "Günlük Ciro", daily_revenue_text, 1, 1)

# Çıkış butonu
exit_button = tk.Button(root, text="Çıkış", command=root.quit, bg="#ff0000", fg="white", font=("Arial", 12, "bold"), bd=0, padx=20, pady=10)
exit_button.pack(pady=20)

# Pencereyi çalıştır
root.mainloop()