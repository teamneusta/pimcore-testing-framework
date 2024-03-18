INSERT INTO `assets`
VALUES (1, 0, 'folder', '', '/', NULL, 1710529075, 1710529075, 1, 1, NULL, 0, 0);

INSERT INTO `documents`
VALUES (1, 0, 'page', '', '/', 999999, 1, 1710529075, 1710529075, 1, 1, 0);

INSERT INTO `documents_page`
VALUES (1, 'App\\Controller\\DefaultController::defaultAction', '', '', '', '', NULL, NULL, '', NULL, NULL, NULL);

INSERT INTO `objects`
VALUES (1, 0, 'folder', '', '/', 999999, 1, 1710529075, 1710529075, 1, 1, NULL, NULL, NULL, NULL, 0);

INSERT INTO `users` (id, parentId, type, name, admin, active)
VALUES (0, 0, 'user', 'system', 1, 1);
