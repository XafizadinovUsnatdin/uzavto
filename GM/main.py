import subprocess

scripts = [
    'forecast_by_car_sales/scripts/avtotrain.py',
    'forecast_by_car_sales/scripts/avtoforecast.py',
    'forecast_by_car_sales/monthly_summary.py',
    # Jupyter notebookni .py faylga aylantirib ishga tushirish uchun avval .py qilish kerak
    # Agar fayl script formatda bo‘lsa, uni ham qo‘shing, masalan:
    'forecast_hourly_number_customer2.py',  # Jupyter notebookni .py formatga o‘zgartiring!
]

for script in scripts:
    print(f'Running {script}...')
    result = subprocess.run(['python', script], capture_output=True, text=True)
    if result.returncode != 0:
        print(f'Error in {script}:\n{result.stderr}')
        break
    else:
        print(f'{script} finished successfully.\nOutput:\n{result.stdout}\n')
