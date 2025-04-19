DELIMITER $$

CREATE PROCEDURE show_order_details(IN order_id INT)
BEGIN
    SELECT o.order_id, o.status, o.shipping_address, oi.product_id, p.name, oi.quantity, oi.price, 
           (oi.quantity * oi.price) AS total_per_item, SUM(oi.quantity * oi.price) AS total_to_pay
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.order_id = order_id
    GROUP BY oi.product_id;
END $$

DELIMITER ;
