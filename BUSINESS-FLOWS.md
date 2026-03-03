# Business Flows Documentation

This document describes the complete business flows for the Point of Sale and Inventory Management System.

---

## Table of Contents

1. [Sale Flow](#sale-flow)
2. [Purchase Flow](#purchase-flow)
3. [Payment Flow](#payment-flow)
4. [Sale Return Flow](#sale-return-flow)
5. [Purchase Return Flow](#purchase-return-flow)
6. [Stock Transfer Flow](#stock-transfer-flow)
7. [Stock Movement Flow](#stock-movement-flow)

---

## Sale Flow

### Overview

The Sale flow handles customer purchases, from creating a pending sale to completing it and recording payments.

### State Machine

```
┌─────────┐     ┌───────────┐     ┌─────────────┐     ┌───────────┐
│ Pending │ ──► │ Completed │ ──► │   Paid/    │     │ Cancelled │
└─────────┘     └───────────┘     │  Partial   │     └───────────┘
                                   └─────────────┘
```

### Status Transitions

| From Status | Allowed Transitions  | Notes                            |
| ----------- | -------------------- | -------------------------------- |
| Pending     | Completed, Cancelled | Must have items                  |
| Completed   | Cancelled            | Can still cancel (returns stock) |
| Cancelled   | None                 | Terminal state                   |

### Actions

| Action           | Trigger             | Stock Impact          | Payment Impact     |
| ---------------- | ------------------- | --------------------- | ------------------ |
| `CreateSale`     | New sale initiated  | None                  | Unpaid             |
| `AddSaleItem`    | Add product to sale | None                  | N/A                |
| `UpdateSaleItem` | Modify item         | None                  | N/A                |
| `RemoveSaleItem` | Remove item         | None                  | N/A                |
| `CompleteSale`   | Confirm sale        | **Deduct** from batch | Calculate status   |
| `CancelSale`     | Cancel sale         | **Add back** to batch | Rollback payment   |
| `DeleteSale`     | Delete pending sale | None                  | Delete payments    |
| `RecordPayment`  | Receive payment     | N/A                   | Update paid_amount |

### Validations

#### CreateSale

- Must have at least one item
- Batch must exist and belong to the product
- Batch must be in the sale's warehouse
- Each batch must have sufficient stock

#### CompleteSale

- Sale status must allow transition to Completed
- Must have at least one item
- Validates stock availability before deduction

#### CancelSale

- Sale must not already be cancelled
- If completed, stock is returned to batches

#### RecordPayment

- Sale must be Completed
- Payment method must be active
- Cannot exceed maximum overpayment limit (2x total for Sales)

### Reference Number Format

```
SAL-YYYYMMDDHHMMSS-XXXX
Example: SAL-20260227143052-A1B2
```

---

## Purchase Flow

### Overview

The Purchase flow handles inventory procurement from suppliers.

### State Machine

```
┌─────────┐     ┌─────────┐     ┌───────────┐     ┌───────────┐
│ Pending │ ──► │ Ordered │ ──► │ Received  │ ──► │ Cancelled │
└─────────┘     └─────────┘     └───────────┘     └───────────┘
```

### Status Transitions

| From Status | Allowed Transitions          | Notes                      |
| ----------- | ---------------------------- | -------------------------- |
| Pending     | Ordered, Received, Cancelled | Must have items            |
| Ordered     | Received, Cancelled          | -                          |
| Received    | Cancelled                    | Can cancel (removes stock) |
| Cancelled   | Pending                      | Can reopen                 |

### Actions

| Action                  | Trigger         | Stock Impact          | Payment Impact     |
| ----------------------- | --------------- | --------------------- | ------------------ |
| `CreatePurchase`        | New purchase    | None                  | Unpaid             |
| `AddPurchaseItem`       | Add product     | None                  | N/A                |
| `UpdatePurchaseItem`    | Modify item     | None                  | N/A                |
| `RemovePurchaseItem`    | Remove item     | None                  | N/A                |
| `UpdatePurchase`        | Modify header   | None                  | N/A                |
| `MarkPurchaseAsOrdered` | Confirm order   | None                  | N/A                |
| `ReceivePurchase`       | Receive goods   | **Add** to batch      | N/A                |
| `CancelPurchase`        | Cancel purchase | **Remove** from batch | Rollback payment   |
| `RecordPayment`         | Pay supplier    | N/A                   | Update paid_amount |

### Validations

#### CreatePurchase

- Supplier must exist
- Warehouse must exist

#### MarkPurchaseAsOrdered

- Must have at least one item
- Must be in Pending status

#### ReceivePurchase

- Must be in Ordered or Pending status
- Creates or finds batch for each item
- Records stock movement for each item

#### CancelPurchase

- Removes stock from batches
- Only allowed if stock is available

---

## Payment Flow

### Overview

The Payment flow handles recording payments for Sales, Purchases, Sale Returns, and Purchase Returns.

### Morph Relationship

Payments use Laravel's Morph relationship to support multiple payable types:

```php
$payment->payable; // Returns Sale, SaleReturn, Purchase, or PurchaseReturn
```

### Acceptable Payment Statuses

| Payable Type   | Required Status |
| -------------- | --------------- |
| Sale           | Completed       |
| SaleReturn     | Completed       |
| Purchase       | Received        |
| PurchaseReturn | Completed       |

### Actions

| Action                | Trigger                | Notes                  |
| --------------------- | ---------------------- | ---------------------- |
| `RecordPayment`       | Receive/refund payment | Creates Payment record |
| `CreatePaymentMethod` | Add payment method     |                        |
| `UpdatePaymentMethod` | Modify payment method  |                        |
| `DeletePaymentMethod` | Remove payment method  | Must not be in use     |

### Payment Status Calculation

```
Unpaid    → paid_amount = 0
Partial   → 0 < paid_amount < total_amount
Paid      → paid_amount >= total_amount
```

### Overpayment Handling

| Type           | Maximum Allowed | Change Amount           |
| -------------- | --------------- | ----------------------- |
| Sale           | 2x total_amount | Stored in change_amount |
| Purchase       | total_amount    | Rejected if exceeded    |
| SaleReturn     | total_amount    | Rejected if exceeded    |
| PurchaseReturn | total_amount    | Rejected if exceeded    |

### Reference Number Format

```
PAY-YYYYMMDDHHMMSS-XXXX
Example: PAY-20260227143052-C3D4
```

---

## Sale Return Flow

### Overview

The Sale Return flow handles customer returns of purchased items.

### State Machine

```
┌─────────┐     ┌───────────┐
│ Pending │ ──► │ Completed │
└─────────┘     └───────────┘
```

### Status Transitions

| From Status | Allowed Transitions | Notes          |
| ----------- | ------------------- | -------------- |
| Pending     | Completed           | -              |
| Completed   | None                | Terminal state |

### Actions

| Action                    | Trigger         | Stock Impact          | Notes                      |
| ------------------------- | --------------- | --------------------- | -------------------------- |
| `CreateSaleReturn`        | Initiate return | None                  | Links to original Sale     |
| `AddSaleReturnItem`       | Add item        | None                  |                            |
| `UpdateSaleReturnItem`    | Modify item     | None                  |                            |
| `RemoveSaleReturnItem`    | Remove item     | None                  |                            |
| `CompleteSaleReturn`      | Confirm return  | **Add** to batch      | Stock returns to inventory |
| `ProcessSaleReturnRefund` | Issue refund    | N/A                   | Creates negative payment   |
| `RevertSaleReturn`        | Undo return     | **Remove** from batch | Reverses stock movement    |
| `DeleteSaleReturn`        | Delete pending  | None                  |                            |

### Validations

#### CompleteSaleReturn

- Must have at least one item
- Validates quantity doesn't exceed original sale quantity
- Stock is added back to warehouse batches

#### ProcessSaleReturnRefund

- Must be Completed
- Creates Payment with negative amount

---

## Purchase Return Flow

### Overview

The Purchase Return flow handles returning goods to suppliers.

### State Machine

```
┌─────────┐     ┌───────────┐
│ Pending │ ──► │ Completed │
└─────────┘     └───────────┘
```

### Status Transitions

| From Status | Allowed Transitions | Notes          |
| ----------- | ------------------- | -------------- |
| Pending     | Completed           | -              |
| Completed   | None                | Terminal state |

### Actions

| Action                        | Trigger         | Stock Impact          | Notes                        |
| ----------------------------- | --------------- | --------------------- | ---------------------------- |
| `CreatePurchaseReturn`        | Initiate return | None                  | Links to original Purchase   |
| `AddPurchaseReturnItem`       | Add item        | None                  |                              |
| `UpdatePurchaseReturnItem`    | Modify item     | None                  |                              |
| `RemovePurchaseReturnItem`    | Remove item     | None                  |                              |
| `CompletePurchaseReturn`      | Confirm return  | **Remove** from batch | Stock removed from inventory |
| `ProcessPurchaseReturnRefund` | Receive refund  | N/A                   | Creates Payment              |
| `RevertPurchaseReturn`        | Undo return     | **Add** back to batch | Reverses stock movement      |
| `DeletePurchaseReturn`        | Delete pending  | None                  |                              |

---

## Stock Transfer Flow

### Overview

The Stock Transfer flow handles moving inventory between warehouses.

### State Machine

```
┌─────────┐     ┌───────────┐     ┌───────────┐
│ Pending │ ──► │ Completed │     │ Cancelled │
└─────────┘     └───────────┘     └───────────┘
```

### Status Transitions

| From Status | Allowed Transitions  | Notes          |
| ----------- | -------------------- | -------------- |
| Pending     | Completed, Cancelled | -              |
| Completed   | None                 | Terminal state |
| Cancelled   | None                 | Terminal state |

### Actions

| Action                        | Trigger          | Stock Impact                                 | Notes                      |
| ----------------------------- | ---------------- | -------------------------------------------- | -------------------------- |
| `CreateStockTransfer`         | New transfer     | None                                         | From source to destination |
| `AddItemToStockTransfer`      | Add item         | None                                         |                            |
| `UpdateStockTransferItem`     | Modify item      | None                                         |                            |
| `RemoveItemFromStockTransfer` | Remove item      | None                                         |                            |
| `UpdateStockTransfer`         | Modify header    | None                                         |                            |
| `CompleteStockTransfer`       | Confirm transfer | **Out** from source<br>**In** to destination | Two-way stock movement     |
| `CancelStockTransfer`         | Cancel transfer  | None                                         | Only if Pending            |

### Stock Movement on Complete

When a transfer is completed:

1. Stock is **deducted** from source warehouse (Out movement)
2. Stock is **added** to destination warehouse (In movement)
3. Batch is created/found in destination warehouse

---

## Stock Movement Flow

### Overview

Stock Movement provides automatic tracking of all inventory changes in the system.

### Actions

| Action                | Trigger          | Type                |
| --------------------- | ---------------- | ------------------- |
| `RecordStockMovement` | Any stock change | In, Out, Adjustment |

### Movement Types

| Type       | Description                                               |
| ---------- | --------------------------------------------------------- |
| In         | Stock added (Purchase, Sale Return, Stock Transfer In)    |
| Out        | Stock removed (Sale, Purchase Return, Stock Transfer Out) |
| Adjustment | Manual stock correction                                   |

### Tracked Operations

| Operation              | Movement Type            | Quantity Change |
| ---------------------- | ------------------------ | --------------- |
| CompleteSale           | Out                      | Negative        |
| CancelSale             | In                       | Positive        |
| ReceivePurchase        | In                       | Positive        |
| CancelPurchase         | Out                      | Negative        |
| CompleteSaleReturn     | In                       | Positive        |
| RevertSaleReturn       | Out                      | Negative        |
| CompletePurchaseReturn | Out                      | Negative        |
| RevertPurchaseReturn   | In                       | Positive        |
| CompleteStockTransfer  | Out (source) / In (dest) | Both            |
| Batch Update           | Adjustment               | +/-             |

### Stock Movement Record

Each stock movement records:

- Warehouse
- Product
- Type (In/Out/Adjustment)
- Quantity
- Previous quantity
- Current quantity
- Reference (Sale, Purchase, etc.)
- Batch
- User
- Note

---

## Summary Table

| Module         | Statuses                                 | Key Actions                                           | Stock Impact                       |
| -------------- | ---------------------------------------- | ----------------------------------------------------- | ---------------------------------- |
| Sale           | Pending → Completed → Cancelled          | Create, Complete, Cancel, RecordPayment               | Deduct on Complete, Add on Cancel  |
| Purchase       | Pending → Ordered → Received → Cancelled | Create, MarkAsOrdered, Receive, Cancel, RecordPayment | Add on Receive, Remove on Cancel   |
| SaleReturn     | Pending → Completed                      | Create, Complete, ProcessRefund, Revert               | Add on Complete, Remove on Revert  |
| PurchaseReturn | Pending → Completed                      | Create, Complete, ProcessRefund, Revert               | Remove on Complete, Add on Revert  |
| StockTransfer  | Pending → Completed/Cancelled            | Create, Complete, Cancel                              | Out from source, In to destination |
| StockMovement  | Automatic                                | RecordStockMovement                                   | All inventory changes tracked      |
