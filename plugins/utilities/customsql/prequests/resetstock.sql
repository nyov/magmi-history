-- test multi request;
UPDATE [[tn:cataloginventory_stock_item]] SET qty=[[qty/quantity]] WHERE product_id REGEXP [[sku_regexp/target sku regexp]];
