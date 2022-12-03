<?php

namespace App\Enums;

use App\Traits\EnumHelper;

enum AccountType: string
{
    use EnumHelper;

    case ASSETS = 'ASSETS';
        case CURRENT_ASSET = 'CURRENT_ASSET';
            case CASH_AND_BANKS = 'CASH_AND_BANKS';
            case RECEIVABLES = 'RECEIVABLES';
                case REVENUE_RECEIVABLE = 'REVENUE_RECEIVABLE';
                case OTHER_RECEIVABLE = 'OTHER_RECEIVABLE';
                case PURCHASE_ORDER_DOWN_PAYMENT_RECEIVABLE = 'PURCHASE_ORDER_DOWN_PAYMENT_RECEIVABLE';
                case PURCHASE_REFUND_RECEIVABLE = 'PURCHASE_REFUND_RECEIVABLE';
                case PURCHASE_RETURN_RECEIVABLE = 'PURCHASE_RETURN_RECEIVABLE';
                case ASSET_SALES_RECEIVABLE = 'ASSET_SALES_RECEIVABLE';
                case SALES_RECEIVABLE = 'SALES_RECEIVABLE';
                case VAT_RECEIVABLE = 'VAT_RECEIVABLE';
            case PREPAID_EXPENSE = 'PREPAID_EXPENSE';
            case INVENTORY = 'INVENTORY';
        case FIXED_ASSET = 'FIXED_ASSET';
    case LIABILITIES = 'LIABILITIES';
        case SHORT_TERM_LIABILITY = 'SHORT_TERM_LIABILITY';
            case EXPENSE_PAYABLE = 'EXPENSE_PAYABLE';
            case PREPAID_EXPENSE_PAYABLE = 'PREPAID_EXPENSE_PAYABLE';
            case OTHER_PAYABLE = 'OTHER_PAYABLE';
            case SALES_ORDER_DOWN_PAYMENT_PAYABLE = 'SALES_ORDER_DOWN_PAYMENT_PAYABLE';
            case PURCHASE_PAYABLE = 'PURCHASE_PAYABLE';
            case PRODUCTION_PROCESS_DIRECT_COST_PAYABLE = 'PRODUCTION_PROCESS_DIRECT_COST_PAYABLE';
            case PRODUCTION_PROCESS_PERIOD_COST_PAYABLE = 'PRODUCTION_PROCESS_PERIOD_COST_PAYABLE';
            case ASSET_PURCHASE_PAYABLE = 'ASSET_PURCHASE_PAYABLE';
            case SALES_ADDITIONAL_EXPENSE_PAYABLE = 'SALES_ADDITIONAL_EXPENSE_PAYABLE';
            case SALES_RETURNS_PAYABLE = 'SALES_RETURNS_PAYABLE';
            case VAT_PAYABLE = 'VAT_PAYABLE';
        case LONG_TERM_LIABILITY = 'LONG_TERM_LIABILITY';
            case OTHER_DEBT = 'OTHER_DEBT';
            case ASSET_PURCHASE_DEBT = 'ASSET_PURCHASE_DEBT';
    case EQUITY = 'EQUITY';
        case CAPITAL = 'CAPITAL';
        case INCOME_STATEMENT_ACCOUNT = 'INCOME_STATEMENT_ACCOUNT';
            case RETAINED_EARNING = 'RETAINED_EARNING';
            case CURRENT_MONTH_PROFIT = 'CURRENT_MONTH_PROFIT';
            case CURRENT_YEAR_PROFIT = 'CURRENT_YEAR_PROFIT';
    case OPERATING_REVENUES = 'OPERATING_REVENUES';
        case SALES = 'SALES';
            case PRODUCT_SALES = 'PRODUCT_SALES';
            case SERVICE_SALES = 'SERVICE_SALES';
            case SALES_DISCOUNTS = 'SALES_DISCOUNTS';
            case SALES_VALUE_ADDED = 'SALES_VALUE_ADDED';
            case SALES_ROUNDING = 'SALES_ROUNDING';
            case SALES_RETURNS = 'SALES RETURNS';
        case OPERATING_REVENUES_USER_SET_INCOME_GROUP = 'OPERATING_REVENUES_USER_SET_INCOME_GROUP';
        case RECEIVABLE_REVENUE = 'RECEIVABLE_REVENUE';
    case COST_OF_GOODS_SOLD = 'COST_OF_GOODS_SOLD';
        case SALES_COST_OF_GOODS_SOLD = 'SALES_COST_OF_GOODS_SOLD';
        case PURCHASE_SUMMARY = 'PURCHASE_SUMMARY';
            case GROSS_PURCHASE = 'GROSS_PURCHASE';
            case PURCHASE_DISCOUNT = 'PURCHASE_DISCOUNT';
            case ADDITIONAL_PURCHASE_COST = 'ADDITIONAL_PURCHASE_COST';
            case PURCHASE_REFUND = 'PURCHASE_REFUND';
            case PURCHASE_RETURN = 'PURCHASE_RETURN';
    case OPERATING_EXPENSES = 'OPERATING_EXPENSES';
        case OPERATING_EXPENSES_USER_SET_EXPENSE_GROUP = 'OPERATING_EXPENSES_USER_SET_EXPENSE_GROUP';
        case DEBT_COST = 'DEBT_COST';
        case DEPRECIATION_LOAD = 'DEPRECIATION_LOAD';
    case OTHER_INCOMES = 'OTHER_INCOMES';
        case OTHER_INCOMES_USER_SET_INCOME_GROUP = 'OTHER_INCOMES_USER_SET_INCOME_GROUP';
        case OTHER_INCOMES_STOCK_ADJUSTMENT_DIFFERENCE = 'OTHER_INCOMES_STOCK_ADJUSTMENT_DIFFERENCE';
        case OTHER_INCOMES_ASSET_ADJUSTMENT_DIFFERENCE = 'OTHER_INCOMES_ASSET_ADJUSTMENT_DIFFERENCE';
        case OTHER_INCOMES_ASSET_SALES_DIFFERENCE = 'OTHER_INCOMES_ASSET_SALES_DIFFERENCE';
        case OTHER_INCOMES_PRODUCTION_PROCESS_DIFFERENCE = 'OTHER_INCOMES_PRODUCTION_PROCESS_DIFFERENCE';
        case OTHER_INCOMES_VAT_RECEIVABLE_ADJUSTMENT = 'OTHER_INCOMES_VAT_RECEIVABLE_ADJUSTMENT';
    case OTHER_EXPENSES = 'OTHER_EXPENSES';
        case OTHER_EXPENSES_USER_SET_EXPENSE_GROUP = 'OTHER_EXPENSES_USER_SET_EXPENSE_GROUP';
        case OTHER_EXPENSES_STOCK_ADJUSTMENT_DIFFERENCE = 'OTHER_EXPENSES_STOCK_ADJUSTMENT_DIFFERENCE';
        case OTHER_EXPENSES_ASSET_ADJUSTMENT_DIFFERENCE = 'OTHER_EXPENSES_ASSET_ADJUSTMENT_DIFFERENCE';
        case OTHER_EXPENSES_ASSET_SALES_DIFFERENCE = 'OTHER_EXPENSES_ASSET_SALES_DIFFERENCE';
        case OTHER_EXPENSES_PRODUCTION_PROCESS_DIFFERENCE = 'OTHER_EXPENSES_PRODUCTION_PROCESS_DIFFERENCE';
        case OTHER_EXPENSES_VAT_PAYABLE_ADJUSTMENT = 'OTHER_EXPENSES_VAT_PAYABLE_ADJUSTMENT';
    case PROFIT_AND_LOSS_SUMMARY = 'PROFIT_AND_LOSS_SUMMARY';
    }
