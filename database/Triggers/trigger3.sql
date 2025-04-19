DELIMITER $$

CREATE TRIGGER restore_stock_on_order_cancellation
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'canceled' THEN
        UPDATE products p
        JOIN order_items oi ON p.product_id = oi.product_id
        SET p.stock_quantity = p.stock_quantity + oi.quantity
        WHERE oi.order_id = NEW.order_id;
    END IF;
END $$

DELIMITER ;
