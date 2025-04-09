import tkinter as tk
from tkinter import ttk
import requests
import matplotlib.pyplot as plt
from matplotlib.backends.backend_tkagg import FigureCanvasTkAgg

# Veri çekme fonksiyonu
def fetch_data():
    try:
        response = requests.get('http://localhost/proje/stats.php')
        return response.json()
    except Exception as e:
        error_label.config(text=f"Veri çekme hatası: {e}")
        return None

# Verileri yenileme fonksiyonu
def refresh_data():
    global data
    data = fetch_data()
    if data:
        update_chart()
        update_table("top_sold")  # Varsayılan olarak en çok satılan ürünleri göster

# Tkinter penceresi
root = tk.Tk()
root.title("Mağaza Veri Analizi")
root.geometry("1000x700")
root.minsize(900, 600)
root.configure(bg="#e0e0e0")

# Tema
style = ttk.Style()
style.theme_use('clam')

# Başlık
title_label = tk.Label(root, text="Mağaza Veri Analizi", font=("Helvetica", 18, "bold"), bg="#e0e0e0", fg="#2c3e50")
title_label.pack(pady=15)

# Hata mesajı için etiket
error_label = tk.Label(root, text="", fg="red", bg="#e0e0e0", font=("Helvetica", 10))
error_label.pack()

# Buton çerçevesi
button_frame = tk.Frame(root, bg="#e0e0e0")
button_frame.pack(pady=10)

# Tablo çerçevesi
table_frame = ttk.Frame(root)
table_frame.pack(pady=10, fill="both", expand=True)

# Tablo oluşturma
tree = ttk.Treeview(table_frame, columns=("Name", "Count"), show="headings", height=5)
tree.heading("Name", text="Ürün Adı")
tree.heading("Count", text="Değer")
tree.column("Name", width=500)
tree.column("Count", width=200)
tree.pack(fill="both", expand=True)

# Araç ipucu (tooltip) fonksiyonu
def create_tooltip(widget, text):
    tooltip = None
    def show_tooltip(e):
        nonlocal tooltip
        x, y = widget.winfo_pointerxy()
        tooltip = tk.Toplevel(widget)
        tooltip.wm_overrideredirect(True)
        tooltip.wm_geometry(f"+{x+10}+{y+10}")
        label = tk.Label(tooltip, text=text, bg="#ffffe0", fg="black", relief="solid", borderwidth=1, font=("Helvetica", 9))
        label.pack()
    def hide_tooltip(e):
        nonlocal tooltip
        if tooltip:
            tooltip.destroy()
            tooltip = None
    widget.bind("<Enter>", show_tooltip)
    widget.bind("<Leave>", hide_tooltip)

# Grafik oluşturma fonksiyonu
def create_chart():
    global canvas
    if 'canvas' in globals():
        canvas.get_tk_widget().destroy()
    fig, ax = plt.subplots(figsize=(6, 4))
    names = [item['name'] for item in data['top_sold']]
    sold = [item['total_sold'] for item in data['top_sold']]
    ax.bar(names, sold, color="#3498db")
    ax.set_title("En Çok Satılan Ürünler", fontsize=12, color="#2c3e50")
    ax.set_ylabel("Satış Adedi", fontsize=10)
    plt.xticks(rotation=45, ha="right", fontsize=8)
    canvas = FigureCanvasTkAgg(fig, master=root)
    canvas.draw()
    canvas.get_tk_widget().pack(pady=10)

# Tabloyu güncelleme fonksiyonu
def update_table(data_type):
    for i in tree.get_children():
        tree.delete(i)
    if data:
        if data_type == "top_sold":
            tree.heading("Count", text="Satış Adedi")
            for item in data['top_sold']:
                tree.insert("", "end", values=(item['name'], f"{item['total_sold']} adet"))
        elif data_type == "least_sold":
            tree.heading("Count", text="Satış Adedi")
            for item in data['least_sold']:
                tree.insert("", "end", values=(item['name'], f"{item['total_sold']} adet"))
        elif data_type == "top_viewed":
            tree.heading("Count", text="Görüntülenme")
            for item in data['top_viewed']:
                tree.insert("", "end", values=(item['name'], f"{item['view_count']} görüntülenme"))
        elif data_type == "top_category":
            tree.heading("Count", text="Satış Adedi")
            tree.insert("", "end", values=(data['top_category']['name'], f"{data['top_category']['total_sold']} adet"))
        elif data_type == "daily_revenue":
            tree.heading("Count", text="Ciro")
            tree.insert("", "end", values=("Günlük Ciro", f"{data['daily_revenue'] if data['daily_revenue'] else 0}₺"))

# Buton oluşturma fonksiyonu
def create_button(parent, text, command, row, col):
    button = tk.Button(parent, text=text, command=command, bg="#3498db", fg="white", font=("Helvetica", 12, "bold"), bd=0, padx=20, pady=10)
    button.grid(row=row, column=col, padx=10, pady=10)
    button.bind("<Enter>", lambda e: button.config(bg="#2980b9"))
    button.bind("<Leave>", lambda e: button.config(bg="#3498db"))
    create_tooltip(button, text)

# Verileri çek ve başlangıçta yerleştir
data = fetch_data()
if data:
    # Butonlar
    create_button(button_frame, "En Çok Satılan Ürün", lambda: update_table("top_sold"), 0, 0)
    create_button(button_frame, "En Az Satılan Ürün", lambda: update_table("least_sold"), 0, 1)
    create_button(button_frame, "En Çok Görüntülenen Ürün", lambda: update_table("top_viewed"), 0, 2)
    create_button(button_frame, "En Çok Tercih Edilen Kategori", lambda: update_table("top_category"), 1, 0)
    create_button(button_frame, "Günlük Ciro", lambda: update_table("daily_revenue"), 1, 1)

    # Varsayılan olarak en çok satılan ürünleri göster
    update_table("top_sold")
    create_chart()

# Alt butonlar (Yenile ve Çıkış)
bottom_button_frame = tk.Frame(root, bg="#e0e0e0")
bottom_button_frame.pack(pady=20)

refresh_button = tk.Button(bottom_button_frame, text="Verileri Yenile", command=refresh_data, bg="#3498db", fg="white", font=("Helvetica", 12, "bold"), bd=0, padx=20, pady=10)
refresh_button.pack(side=tk.LEFT, padx=10)
refresh_button.bind("<Enter>", lambda e: refresh_button.config(bg="#2980b9"))
refresh_button.bind("<Leave>", lambda e: refresh_button.config(bg="#3498db"))
create_tooltip(refresh_button, "Verileri yeniden yükle")

exit_button = tk.Button(bottom_button_frame, text="Çıkış", command=root.quit, bg="#e74c3c", fg="white", font=("Helvetica", 12, "bold"), bd=0, padx=20, pady=10)
exit_button.pack(side=tk.LEFT, padx=10)
exit_button.bind("<Enter>", lambda e: exit_button.config(bg="#c0392b"))
exit_button.bind("<Leave>", lambda e: exit_button.config(bg="#e74c3c"))
create_tooltip(exit_button, "Uygulamadan çık")

# Pencereyi çalıştır
root.mainloop()