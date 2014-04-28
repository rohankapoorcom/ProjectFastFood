CREATE OR REPLACE VIEW averages AS
SELECT AVG(TIME_TO_SEC(TIMEDIFF(orders.timeArrived, orders.timePlaced))) AS wait_time,
	 AVG(orders.price) AS price,
	 orders.restaurant
FROM orders 
GROUP BY orders.restaurant;

SELECT restaurants.name, 
	SEC_TO_TIME(averages.wait_time) AS average_wait_time, 
	averages.price
FROM averages
INNER JOIN restaurants 
ON averages.restaurant = restaurants.id
ORDER BY wait_time ASC, price ASC
LIMIT 10;