#!/bin/bash

echo "Fixing Hyperf to Hypervel namespaces..."

# Find all PHP files in app/ and config/ directories
find app/ config/ -name "*.php" -type f | while read file; do
    # Make a backup
    cp "$file" "$file.bak"
    
    # Replace common namespace patterns
    sed -i \
        -e 's/use Hyperf\\Foundation\\Http\\Kernel/use Hypervel\\Foundation\\Http\\Kernel/g' \
        -e 's/use Hyperf\\Foundation\\Http\\FormRequest/use Hypervel\\Foundation\\Http\\FormRequest/g' \
        -e 's/use Hyperf\\Foundation\\Http\\Middleware\\[^;]*/use Hypervel\\Foundation\\Http\\Middleware\\&/g' \
        -e 's/use Hyperf\\Support\\Str/use Hypervel\\Support\\Str/g' \
        -e 's/Str::/Hypervel\\Support\\Str::/g' \
        -e 's/use Hyperf\\Cache\\SwooleStore/use Hypervel\\Cache\\SwooleStore/g' \
        -e 's/SwooleStore::/Hypervel\\Cache\\SwooleStore::/g' \
        -e 's/use Hyperf\\Auth\\Middleware\\Authorize/use Hypervel\\Auth\\Middleware\\Authorize/g' \
        -e 's/use Hyperf\\Auth\\Middleware\\Authenticate/use Hypervel\\Auth\\Middleware\\Authenticate/g' \
        -e 's/use Hyperf\\Router\\Middleware\\ThrottleRequests/use Hypervel\\Router\\Middleware\\ThrottleRequests/g' \
        -e 's/use Hyperf\\Router\\Middleware\\SubstituteBindings/use Hypervel\\Router\\Middleware\\SubstituteBindings/g' \
        -e 's/use Hyperf\\Router\\Middleware\\ValidateSignature/use Hypervel\\Router\\Middleware\\ValidateSignature/g' \
        -e 's/use Hyperf\\Support\\ServiceProvider/use Hypervel\\Support\\ServiceProvider/g' \
        -e 's/use Hyperf\\HttpServer\\Contract\\RequestInterface/use Hyperf\\HttpServer\\Contract\\RequestInterface/g' \
        -e 's/use Hyperf\\HttpServer\\Contract\\ResponseInterface/use Hyperf\\HttpServer\\Contract\\ResponseInterface/g' \
        -e 's/use Hyperf\\HttpMessage\\Stream\\SwooleStream/use Hyperf\\HttpMessage\\Stream\\SwooleStream/g' \
        -e 's/RequestInterface::/Hypervel\\HttpServer\\Contract\\RequestInterface::/g' \
        -e 's/ResponseInterface::/Hypervel\\HttpServer\\Contract\\ResponseInterface::/g' \
        "$file"
    
    # Remove backup if file changed
    if ! diff -q "$file" "$file.bak" 2>/dev/null; then
        rm "$file.bak"
        echo "Fixed: $file"
    else
        rm "$file.bak"
    fi
done

echo "Done!"
