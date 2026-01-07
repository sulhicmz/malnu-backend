#!/bin/bash

# Fix routes files to use Hypervel Router::group() signature
# signature: group(string $prefix, callable|string $source, array $options = [])

for file in routes/api.php routes/web.php; do
    if [ ! -f "$file" ]; then
        continue
    fi
    
    echo "Processing $file..."
    
    # For routes/api.php and routes/web.php, there are likely no top-level prefix
    # Just remove the middleware array from the first argument
    
    # Pattern: Route::group(['middleware' => [...]], function () {
    # Should become: Route::group('', function () {, ['middleware' => [...]]
    
    # Use PHP to restructure the file properly
    php -r '
    $content = file_get_contents("'"$file"'");
    
    // Pattern 1: Route::group(["middleware" => [...]], function () {
    // Fix: Route::group("", function () {, ["middleware" => [...]]
    $content = preg_replace(
        "/Route::group\(\[\'middleware\'\s*=>\s*\[[^]]+\]\],\s*function\s*\(\)\s*\{/",
        "Route::group(\"\", function () {, [\"middleware\" => ",
        $content
    );
    
    file_put_contents("'"$file"'", $content);
    echo "Fixed: $file\n";
    '
done

echo "Done!"
