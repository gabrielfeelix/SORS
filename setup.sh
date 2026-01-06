#!/bin/bash

# Aguardar Sail estar pronto
echo "Aguardando Sail iniciar..."
for i in {1..60}; do
  if ./vendor/bin/sail artisan version > /dev/null 2>&1; then
    echo "Sail está pronto!"
    break
  fi
  echo "Tentativa $i/60..."
  sleep 5
done

# Gerar chave da aplicação
echo "Gerando chave da aplicação..."
./vendor/bin/sail artisan key:generate

# Executar migrations com seed
echo "Executando migrations..."
./vendor/bin/sail artisan migrate --seed

# Instalar dependências do Node.js
echo "Instalando dependências do Node.js..."
./vendor/bin/sail npm install

echo "Setup concluído!"
