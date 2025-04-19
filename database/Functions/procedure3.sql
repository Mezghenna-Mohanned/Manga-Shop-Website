DELIMITER $$

CREATE PROCEDURE show_order_history(IN user_id INT)
BEGIN
    SELECT o.order_id, o.status, o.total_price, o.shipping_address, o.created_at
    FROM orders o
    WHERE o.user_id = user_id
    ORDER BY o.created_at DESC;
END $$

DELIMITER ;
