DELIMITER $$

CREATE PROCEDURE finalize_order(IN user_id INT)
BEGIN
    UPDATE orders SET status = 'completed' WHERE user_id = user_id AND status = 'pending';
    
    DELETE FROM shopping_cart WHERE user_id = user_id;

    UPDATE products p
    JOIN order_items oi ON p.product_id = oi.product_id
    SET p.stock_quantity = p.stock_quantity - oi.quantity
    WHERE oi.order_id = (SELECT order_id FROM orders WHERE user_id = user_id AND status = 'completed');
END $$

DELIMITER ;
