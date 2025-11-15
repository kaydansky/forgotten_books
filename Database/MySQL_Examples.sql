-- 1. Upload new Book by some user (ID 1) --
-- INSERT INTO queue
-- (pibn, added_by_user, queue_number, added, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn)
-- VALUES
-- (IF((SELECT MAX(id) FROM queue AS b) IS NULL, 0, (SELECT MAX(id) FROM queue AS b)) + 1, 1, 1, UNIX_TIMESTAMP(), 'Robinson Cruzo', 'Fantasy', 'Daniel Default', 'Charles Dickens', 1, 2, 6543, 654, 'r9Yew876');

INSERT INTO queue
(pibn, added_by_user, queue_number, added, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, comments)
VALUES
(12345, 1, 1, UNIX_TIMESTAMP(), 'Robinson Cruzo', 'Fantasy', 'Daniel Default', 'Charles Dickens', 1, 2, 6543, 654, 'test');

-- 2. Get Book intended for Queue # --
-- Set `queue_number` value to appropriate Queue number --
SELECT * FROM queue t1 WHERE added IS NOT NULL AND queue_number = 1 AND id = (SELECT MAX(id) FROM queue WHERE pibn = t1.pibn) ORDER BY added ASC LIMIT 1;

-- 3. Request Book on Queue 1 by Proofreader (ID 2) --
INSERT INTO queue
(pibn, requested_by_user, queue_number, assigned, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number)
SELECT pibn, 2, queue_number, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = 1);

-- 4. Complete Queue 1 by Proofreader (ID 2)  --
-- Move Book to Queue 2 --
-- Request Supervisor Attention --
UPDATE queue SET completed = UNIX_TIMESTAMP(), title = 'Robinson Crusoe gone by the snow', subtitle = 'Adventures', author1 = 'Daniel Defaut', author2 = 'Lewis Carol', volume = 2, volumes_total = 3, word_count = 5436545, images_number = 356 WHERE id = 2;
INSERT INTO queue
(pibn, added_by_user, queue_number, added, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, supervisor_attention)
SELECT pibn, 2, 2, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, 1
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = 1);

-- 5. Request Book on Queue 2 by Proofreader Supervisor (ID 3) --
INSERT INTO queue
(pibn, requested_by_user, queue_number, assigned, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn)
SELECT pibn, 3, queue_number, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = 1);

-- 6. Too many errors, return by Proofreader Supervisor (ID 3) to Proofing Queue, re-assign to Proofreader (ID 2) --
INSERT INTO queue
(pibn, added_by_user, requested_by_user, queue_number, assigned, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn, comments, too_many_errors)
SELECT pibn, 3, 2, 1, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn, 'Nothing but errors', 1
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = 1);

-- 7. Fixed and Complete again Queue 1 by Proofreader (ID 2) --
-- Move Book to Queue 2 --
-- Comments added --
UPDATE queue SET completed = UNIX_TIMESTAMP(), title = 'Robinson gone by the wind', subtitle = 'Adventures', author1 = 'Daniel Default', author2 = 'Lewis Carol', volume = 2, volumes_total = 3, word_count = 5436545, images_number = 356, pibn = 'r43GfD54' WHERE id = 5;
INSERT INTO queue
(pibn, added_by_user, queue_number, added, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn, comments)
SELECT pibn, 2, 2, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn, 'Fixed all errors'
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = 1);

-- 8. Request again Book on Queue 2 by Proofreader Supervisor (ID 3) --
INSERT INTO queue
(pibn, requested_by_user, queue_number, assigned, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn)
SELECT pibn, 3, queue_number, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = 1);

-- 9. Complete Queue 2 --
-- Add Comments --
-- Move Book to Queue 9 --
-- Request Coordinator Attention --
UPDATE queue SET completed = UNIX_TIMESTAMP(), title = 'Robison Cruso gone by the wind', subtitle = 'Poetry', author1 = 'Lewis Carol', author2 = 'A.Conandoil', volume = 2, volumes_total = 3, word_count = 5436545, images_number = 356, pibn = 'r43GfD54' WHERE id = 7;
INSERT INTO queue
(pibn, added_by_user, queue_number, added, title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn, comments, coordinator_attention)
SELECT pibn, 3, 9, UNIX_TIMESTAMP(), title, subtitle, author1, author2, volume, volumes_total, word_count, images_number, pibn, 'Coordinator attention required', 1
FROM queue
WHERE id = (SELECT MAX(id) FROM queue WHERE pibn = 1);


-- 10. Show work path for Book --
SELECT t1.*, t2.first_name AS AddedByFirstName, t2.last_name AS AddedByLastName, t3.first_name AS RequestedByFirstName, t3.last_name AS RequestedByLastName FROM queue t1 LEFT JOIN workers t2 ON t1.added_by_user = t2.user_id LEFT JOIN workers t3 ON t1.requested_by_user = t3.user_id WHERE t1.pibn = 1 AND t1.assigned IS NOT NULL OR (t1.pibn = 1 AND t1.comments IS NOT NULL) ORDER BY t1.id;

-- 11. Choose Book to view as Coordinator --
-- Select last record for given Book ID assuming it is mostly completed version at the moment --
SELECT * FROM queue WHERE pibn = 1 ORDER BY id DESC LIMIT 1;

-- 12. View all Books worked by Worker ID 3 --
-- Fetch all associated comments --
SELECT pibn, MAX(id) as id, MAX(added_by_user) as added_by_user, MAX(requested_by_user) as requested_by_user FROM queue WHERE added_by_user = 2 OR requested_by_user = 2 GROUP BY pibn;
SELECT pibn, comments FROM queue WHERE (added_by_user = 2 OR requested_by_user = 2) AND comments IS NOT NULL;


-- View work statistics of Worker ID 2: --

-- 13. Number of Books completed by Worker in last week & 4 weeks (month) --
SELECT COUNT(*) AS BooksCompleted FROM (SELECT COUNT(pibn) FROM queue WHERE requested_by_user = 2 AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 7 DAY) GROUP BY pibn) AS t;
SELECT COUNT(*) AS BooksCompleted FROM (SELECT COUNT(pibn) FROM queue WHERE requested_by_user = 2 AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 1 MONTH) GROUP BY pibn) AS t;

-- 14. Word Count of completed Books in last week & 4 weeks (month) --
SELECT SUM(word_count) AS WordCount FROM queue WHERE id IN (SELECT MAX(id) AS id FROM queue WHERE requested_by_user = 2 AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 7 DAY) GROUP BY pibn);
SELECT SUM(word_count) AS WordCount FROM queue WHERE id IN (SELECT MAX(id) AS id FROM queue WHERE requested_by_user = 2 AND completed IS NOT NULL AND FROM_UNIXTIME(completed) >= DATE_ADD(CURDATE(), INTERVAL - 1 MONTH) GROUP BY pibn);

-- 15. Number of times their work was Rejected by Supervisor in last week & 4 weeks (month) --
SELECT COUNT(id) AS Rejections FROM queue WHERE requested_by_user = 2 AND too_many_errors = 1 AND FROM_UNIXTIME(assigned) >= DATE_ADD(CURDATE(), INTERVAL - 7 DAY);
SELECT COUNT(id) AS Rejections FROM queue WHERE requested_by_user = 2 AND too_many_errors = 1 AND FROM_UNIXTIME(assigned) >= DATE_ADD(CURDATE(), INTERVAL - 1 MONTH);

-- 16. Current Pay Rate --
SELECT base_pay_rate AS PayRate FROM workers WHERE user_id = 2;

-- 17. Date Worker was Registered --
SELECT FROM_UNIXTIME(registered) AS WorkerRegistered FROM users WHERE id = 2;

-- 18. Time/Date of last login --
SELECT FROM_UNIXTIME(last_login) AS WorkerLastLogin FROM users WHERE id = 2;
-- End of work statistics of Worker ID 2: --

-- 19. Change Pay Rate of Worker ID 2 --
UPDATE workers SET base_pay_rate = ? WHERE user_id = 2;

-- 20. Change Position of Worker ID 2 --
UPDATE users SET roles_mask = ? WHERE id = 2;

-- 21. View all Completed Books --
SELECT pibn, MAX(id) as id, MAX(queue_number) as queue_number, MAX(completed) as completed FROM queue WHERE queue_number = 10 GROUP BY pibn;

-- 22. View all Removed  Books --
SELECT pibn, MAX(id) as id, MAX(queue_number) as queue_number, MAX(completed) as completed FROM queue WHERE queue_number = 11 GROUP BY pibn;