@echo off
echo "=== Solucion para error de memoria en Node.js ==="
echo.

echo "1. Configurando memoria aumentada para Node.js..."
set NODE_OPTIONS=--max-old-space-size=8192

echo "2. Limpiando cache de npm..."
npm cache clean --force

echo "3. Verificando estado de dependencias..."
npm outdated

echo.
echo "=== Comandos disponibles ==="
echo "Para actualizar dependencias menores:"
echo "  npm update"
echo.
echo "Para actualizar Angular (si es necesario):"
echo "  ng update @angular/core"
echo.
echo "Para verificar memoria disponible:"
echo "  node -e \"console.log('Memoria:', process.memoryUsage())\""
echo.
echo "=== Listo! ==="
