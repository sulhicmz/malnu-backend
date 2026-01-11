<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Monetization\MarketplaceProduct;
use App\Models\Monetization\Transaction;
use App\Models\Monetization\TransactionItem;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class MonetizationController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function indexProducts()
    {
        try {
            $query = MarketplaceProduct::query();

            $category = $this->request->query('category');
            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($category) {
                $query->where('category', $category);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $products = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($products, 'Marketplace products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeProduct()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name', 'price', 'category'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $product = MarketplaceProduct::create($data);

            return $this->successResponse($product, 'Marketplace product created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PRODUCT_CREATION_ERROR', null, 400);
        }
    }

    public function showProduct(string $id)
    {
        try {
            $product = MarketplaceProduct::find($id);

            if (!$product) {
                return $this->notFoundResponse('Marketplace product not found');
            }

            return $this->successResponse($product, 'Marketplace product retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateProduct(string $id)
    {
        try {
            $product = MarketplaceProduct::find($id);

            if (!$product) {
                return $this->notFoundResponse('Marketplace product not found');
            }

            $data = $this->request->all();
            $product->update($data);

            return $this->successResponse($product, 'Marketplace product updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PRODUCT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyProduct(string $id)
    {
        try {
            $product = MarketplaceProduct::find($id);

            if (!$product) {
                return $this->notFoundResponse('Marketplace product not found');
            }

            $product->delete();

            return $this->successResponse(null, 'Marketplace product deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PRODUCT_DELETION_ERROR', null, 400);
        }
    }

    public function indexTransactions()
    {
        try {
            $query = Transaction::with(['items']);

            $studentId = $this->request->query('student_id');
            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $transactions = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($transactions, 'Transactions retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeTransaction()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['student_id', 'total_amount'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $transaction = Transaction::create($data);

            return $this->successResponse($transaction, 'Transaction created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSACTION_CREATION_ERROR', null, 400);
        }
    }

    public function showTransaction(string $id)
    {
        try {
            $transaction = Transaction::with(['items'])->find($id);

            if (!$transaction) {
                return $this->notFoundResponse('Transaction not found');
            }

            return $this->successResponse($transaction, 'Transaction retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateTransaction(string $id)
    {
        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return $this->notFoundResponse('Transaction not found');
            }

            $data = $this->request->all();
            $transaction->update($data);

            return $this->successResponse($transaction, 'Transaction updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSACTION_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyTransaction(string $id)
    {
        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return $this->notFoundResponse('Transaction not found');
            }

            $transaction->delete();

            return $this->successResponse(null, 'Transaction deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSACTION_DELETION_ERROR', null, 400);
        }
    }

    public function indexTransactionItems()
    {
        try {
            $query = TransactionItem::with(['transaction', 'product']);

            $transactionId = $this->request->query('transaction_id');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($transactionId) {
                $query->where('transaction_id', $transactionId);
            }

            $items = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($items, 'Transaction items retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeTransactionItem()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['transaction_id', 'product_id', 'quantity', 'price'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $item = TransactionItem::create($data);

            return $this->successResponse($item, 'Transaction item created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSACTION_ITEM_CREATION_ERROR', null, 400);
        }
    }

    public function showTransactionItem(string $id)
    {
        try {
            $item = TransactionItem::with(['transaction', 'product'])->find($id);

            if (!$item) {
                return $this->notFoundResponse('Transaction item not found');
            }

            return $this->successResponse($item, 'Transaction item retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateTransactionItem(string $id)
    {
        try {
            $item = TransactionItem::find($id);

            if (!$item) {
                return $this->notFoundResponse('Transaction item not found');
            }

            $data = $this->request->all();
            $item->update($data);

            return $this->successResponse($item, 'Transaction item updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSACTION_ITEM_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyTransactionItem(string $id)
    {
        try {
            $item = TransactionItem::find($id);

            if (!$item) {
                return $this->notFoundResponse('Transaction item not found');
            }

            $item->delete();

            return $this->successResponse(null, 'Transaction item deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSACTION_ITEM_DELETION_ERROR', null, 400);
        }
    }
}
