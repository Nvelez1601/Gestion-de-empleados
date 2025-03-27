import subprocess
import time
import tkinter as tk
from tkinter import messagebox
import threading
import os

# Contraseña para desbloquear
PASSWORD = "1234"

# Variables para controlar el monitoreo
monitoreo_activo = True

# Ruta de la carpeta a bloquear
CARPETA_BLOQUEADA = r"C:\Users\dieho\Desktop\Juegos citra"

# Función para bloquear una carpeta
def bloquear_carpeta(ruta_carpeta):
    try:
        subprocess.run(["icacls", ruta_carpeta, "/deny", "*S-1-1-0:(OI)(CI)(F)"], check=True)
        print(f"Carpeta {ruta_carpeta} bloqueada correctamente.")
    except subprocess.CalledProcessError as e:
        print(f"Error al bloquear la carpeta: {e}")

# Función para desbloquear una carpeta
def desbloquear_carpeta(ruta_carpeta):
    try:
        subprocess.run(["icacls", ruta_carpeta, "/remove:d", "*S-1-1-0"], check=True)
        print(f"Carpeta {ruta_carpeta} desbloqueada correctamente.")
    except subprocess.CalledProcessError as e:
        print(f"Error al desbloquear la carpeta: {e}")

# Función para desbloquear todo
def desbloquear_todo():
    password = entrada_password.get()
    if password == PASSWORD:
        desbloquear_carpeta(CARPETA_BLOQUEADA)  # Desbloquea la carpeta
        messagebox.showinfo("Éxito", "Acceso desbloqueado correctamente.")
        ventana.destroy()  # Cierra la ventana de la interfaz
    else:
        messagebox.showerror("Error", "Contraseña incorrecta.")

# Función para monitorear el acceso a la carpeta
def monitorear_carpeta():
    global monitoreo_activo
    while monitoreo_activo:
        # Verifica si el explorador de archivos está abierto en la carpeta bloqueada
        proceso = subprocess.run(["tasklist", "/fi", "imagename eq explorer.exe"], capture_output=True, text=True)
        if "explorer.exe" in proceso.stdout:
            # Verifica si el explorador está accediendo a la carpeta bloqueada
            try:
                # Lista los procesos de explorador y sus rutas
                procesos = subprocess.run(["wmic", "process", "where", "name='explorer.exe'", "get", "ExecutablePath"], capture_output=True, text=True)
                if CARPETA_BLOQUEADA.lower() in procesos.stdout.lower():
                    mostrar_interfaz()  # Muestra la interfaz para ingresar la contraseña
            except Exception as e:
                print(f"Error al verificar el explorador: {e}")
        time.sleep(1)  # Espera 1 segundo antes de volver a verificar

# Función para mostrar la interfaz gráfica
def mostrar_interfaz():
    global ventana, entrada_password
    ventana = tk.Tk()
    ventana.title("Desbloquear Acceso")
    ventana.geometry("300x150")

    # Etiqueta y campo de contraseña
    tk.Label(ventana, text="Ingrese la contraseña:").pack(pady=10)
    entrada_password = tk.Entry(ventana, show="*")
    entrada_password.pack(pady=10)

    # Botón para desbloquear
    tk.Button(ventana, text="Desbloquear Acceso", command=desbloquear_todo).pack(pady=10)

    # Iniciar la interfaz
    ventana.mainloop()

# Bloquear la carpeta al inicio
bloquear_carpeta(CARPETA_BLOQUEADA)

# Iniciar el monitoreo en un hilo separado
threading.Thread(target=monitorear_carpeta, daemon=True).start()

# Mantener el programa en ejecución
try:
    while True:
        time.sleep(1)  # Evita que el programa principal termine
except KeyboardInterrupt:
    monitoreo_activo = False  # Detiene el monitoreo al cerrar el programa