DELIMITER $$

CREATE TRIGGER log_canceled_orders
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'canceled' THEN
        INSERT INTO canceled_orders_history (order_id, user_id, status, total_price, created_at)
        VALUES (NEW.order_id, NEW.user_id, NEW.status, NEW.total_price, NEW.created_at);
    END IF;
END $$

DELIMITER ;
