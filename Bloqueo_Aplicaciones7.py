import os
import tkinter as tk
from tkinter import messagebox
import shutil
from cryptography.fernet import Fernet
import base64
import hashlib
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
from cryptography.hazmat.backends import default_backend

# Contraseña para desbloquear (debe ser la misma siempre)
PASSWORD = "1234"

# Ruta de la carpeta a proteger
CARPETA_ORIGINAL = r"C:\Users\dieho\Desktop\Prueba de Bloqueo"
CARPETA_TEMPORAL = r"C:\Users\dieho\Desktop\Prueba de Bloqueado_Papuh"

# Generar una clave de cifrado más segura
def generar_clave(password):
    salt = b'salt_fijo_seguro'  # Debería ser único en aplicaciones reales
    kdf = PBKDF2HMAC(
        algorithm=hashes.SHA256(),
        length=32,
        salt=salt,
        iterations=100000,
        backend=default_backend()
    )
    return base64.urlsafe_b64encode(kdf.derive(password.encode()))

# Cifrar una carpeta con manejo de errores
def cifrar_carpeta(ruta_carpeta, clave):
    fernet = Fernet(clave)
    errores = []
    
    for root, _, files in os.walk(ruta_carpeta):
        for file in files:
            ruta_archivo = os.path.join(root, file)
            try:
                # Verificar si el archivo no está vacío
                if os.path.getsize(ruta_archivo) == 0:
                    errores.append(f"Archivo vacío omitido: {ruta_archivo}")
                    continue
                    
                with open(ruta_archivo, "rb") as f:
                    datos = f.read()
                
                datos_cifrados = fernet.encrypt(datos)
                
                with open(ruta_archivo, "wb") as f:
                    f.write(datos_cifrados)
                    
            except Exception as e:
                errores.append(f"Error cifrando {ruta_archivo}: {str(e)}")
                continue
    
    if errores:
        print("\n".join(errores))

# Descifrar una carpeta con manejo de errores
def descifrar_carpeta(ruta_carpeta, clave):
    fernet = Fernet(clave)
    errores = []
    
    for root, _, files in os.walk(ruta_carpeta):
        for file in files:
            ruta_archivo = os.path.join(root, file)
            try:
                with open(ruta_archivo, "rb") as f:
                    datos_cifrados = f.read()
                
                datos_descifrados = fernet.decrypt(datos_cifrados)
                
                with open(ruta_archivo, "wb") as f:
                    f.write(datos_descifrados)
                    
            except Exception as e:
                errores.append(f"Error descifrando {ruta_archivo}: {str(e)}")
                continue
    
    if errores:
        print("\n".join(errores))

# Función para validar la contraseña
def validar_password():
    password = entrada_password.get()
    if password == PASSWORD:
        clave = generar_clave(password)
        try:
            if not os.path.exists(CARPETA_TEMPORAL):
                messagebox.showerror("Error", "No hay carpeta bloqueada para desbloquear.")
                return
                
            # Descifrar la carpeta temporal
            descifrar_carpeta(CARPETA_TEMPORAL, clave)
            
            # Mover los archivos descifrados a la carpeta original
            if os.path.exists(CARPETA_ORIGINAL):
                shutil.rmtree(CARPETA_ORIGINAL)
                
            shutil.move(CARPETA_TEMPORAL, CARPETA_ORIGINAL)
            messagebox.showinfo("Éxito", "Acceso desbloqueado correctamente.")
            ventana.destroy()
            
        except Exception as e:
            messagebox.showerror("Error", f"Error al descifrar: {e}")
            # Intentar restaurar el estado original
            if os.path.exists(CARPETA_TEMPORAL) and not os.path.exists(CARPETA_ORIGINAL):
                shutil.move(CARPETA_TEMPORAL, CARPETA_ORIGINAL)
    else:
        messagebox.showerror("Error", "Contraseña incorrecta.")

# Interfaz gráfica
def mostrar_interfaz():
    global ventana, entrada_password
    ventana = tk.Tk()
    ventana.title("Desbloquear Acceso")
    ventana.geometry("300x150")
    
    tk.Label(ventana, text="Ingrese la contraseña:").pack(pady=10)
    entrada_password = tk.Entry(ventana, show="*")
    entrada_password.pack(pady=10)
    
    tk.Button(ventana, text="Desbloquear Acceso", command=validar_password).pack(pady=10)
    
    ventana.mainloop()

# Bloquear la carpeta con manejo seguro
def bloquear_carpeta():
    if os.path.exists(CARPETA_ORIGINAL):
        try:
            # Verificar si la carpeta temporal ya existe
            if os.path.exists(CARPETA_TEMPORAL):
                shutil.rmtree(CARPETA_TEMPORAL)
                
            # Mover la carpeta original
            shutil.move(CARPETA_ORIGINAL, CARPETA_TEMPORAL)
            
            # Cifrar los archivos
            clave = generar_clave(PASSWORD)
            cifrar_carpeta(CARPETA_TEMPORAL, clave)
            
            print("Carpeta cifrada y bloqueada correctamente.")
        except Exception as e:
            print(f"Error al bloquear carpeta: {e}")
            # Intentar restaurar el estado original
            if os.path.exists(CARPETA_TEMPORAL) and not os.path.exists(CARPETA_ORIGINAL):
                shutil.move(CARPETA_TEMPORAL, CARPETA_ORIGINAL)

# Ejecución principal
if __name__ == "__main__":
    try:
        bloquear_carpeta()
        mostrar_interfaz()
    except Exception as e:
        print(f"Error inesperado: {e}")
        input("Presione Enter para salir...")