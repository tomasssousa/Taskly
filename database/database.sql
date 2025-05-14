PRAGMA FOREIGN KEYS ON;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_image;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS cate_serv;

CREATE TABLE users (
    username VARCHAR PRIMARY KEY,
    name VARCHAR NOTNULL,
    password VARCHAR NOTNULL,
    email VARCHAR NOTNULL UNIQUE,
    admin BOOLEAN NOTNULL
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pricing INTEGER NOTNULL,
    delivery_time VARCHAR,
    description VARCHAR,
    rating INTEGER,
    --status VARCHAR, -- talvez seja na transactions
    user VARCHAR REFERENCES users NOTNULL,
    title VARCHAR NOTNULL
);

CREATE TABLE service_image (
    id varchar PRIMARY KEY AUTOINCREMENT,
    services INTEGER REFERENCES services
);

CREATE TABLE comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    comment VARCHAR,
    service_id INTEGER REFERENCES services,
    user VARCHAR REFERENCES users NOTNULL,
    reply_to INTEGER REFERENCES comments
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user VARCHAR REFERENCES users NOTNULL,
    service_id INTEGER REFERENCES services NOTNULL,
    status VARCHAR NOTNULL,
    rating INTEGER --don't know
);

CREATE TABLE categories (
    category VARCHAR PRIMARY KEY
    --services INTEGER REFERENCES services
);

CREATE TABLE cate_serv (
    service_id INTEGER REFERENCES services,
    category VARCHAR REFERENCES categories,
    PRIMARY KEY (service_id, category_id)
);



--ADMIN USER.  Password can be changed to somethingless secure and quick (!am$9of28FGT-SALT<-funny_joke   OR    1234)
INSERT INTO users (username, name, password, email, admin) VALUES ('admin', 'administrater', '!am$9of28FGT-SALT<-funny_joke', 'siteAdmin@fe.up.pt', 1);

--Regular User.
INSERT INTO users (username, name, password, email, admin) VALUES ('Becky', 'Becky Paw', '!_L0V3_P3T5', 'BeckyP@gmail.com', 0);
INSERT INTO users (username, name, password, email, admin) VALUES ('USA', 'MILITARY', 'setrgh43876%$', 'USAMILITARY@Military.USA', 0);

--Services to fill the site a bit
INSERT INTO services (id, pricing, delivery_time, description, rating, user, title) VALUES (1, 200,'10 days','Hear hear, do you have a dragon and dont know how to properly train him/her? Dont fear beacuse, for a mere 200 money currency I will train your dragon to be obidient to you. Full refund if you end up unsatisfied and the site allows for refunds. PS: it doesnt',5,'admin', 'How to train your dragon');
INSERT INTO services (id, pricing, delivery_time, description, rating, user, title) VALUES (2, 50,'2 days','Beginners course for web design. More information after contact.',4,'admin', 'Learnig web design');
INSERT INTO services (id, pricing, delivery_time, description, rating, user, title) VALUES (3, 10,'Your pick','Hi, my names Becky and i really LOVE pets. Dogs, cat, snakes, hamsters, you say it, i love it. And thats why I am the PERFECT pick for you to leave your pets with when you need to leave the house. For 10 money currency a day I will take of your precious Kids(not humans tho). Send me a message if you need a Petsitter. Byeee.',4,'Becky', 'Petsitting');
INSERT INTO services (id, pricing, delivery_time, description, rating, user, title) VALUES (4, 0,'Max until you are 55','Your country needs you. If you are a young men and unemployed, look no further. Come serve your country under a good salary, a good retirement plan, and a sence of proudness. Join today. PS: Let it be known that lowering our rating helps no one.',1,'USA', 'Military');

--Categories and their connections
INSERT INTO categories (category) Values ('education');
INSERT INTO categories (category) Values ('babysitting');
INSERT INTO categories (category) Values ('spam');

INSERT INTO cate_serv (service_id, category) Values (1,'education');
INSERT INTO cate_serv (service_id, category) Values (2,'education');
INSERT INTO cate_serv (service_id, category) Values (3,'babysitting');
INSERT INTO cate_serv (service_id, category) Values (4,'spam');

--Some comments
INSERT INTO comments (id ,comment, service_id, user) Values(1, 'Join the military today', 3, 'USA');
INSERT INTO comments (id ,comment, user, reply_to) Values(2, 'Sorry. Cant leave my babies alone', 'Becky', 1);
INSERT INTO comments (id, comment, service_id, user) Values(3, 'If you keep spaming, your account will be terminated', 'admin', 1);
INSERT INTO comments (id ,comment, service_id, user) Values(4, 'Great stuff although, as a professional in the area, i think it is somewhat lackluster', 2, 'Becky');
INSERT INTO comments (id ,comment, service_id, user) Values(5, 'Loved it. My dragon now flyes on command and no longer burn the couch. Please message me if you ever open a people trainig on how to train all your pets', 1, 'Becky');

--Some transactions
Insert INTO transactions (id, user, service_id, status, rating) Values(1, 'USA', 3, DONE, 4);
Insert INTO transactions (id, user, service_id, status, rating) Values(2, 'Becky', 1, DONE, 5);
Insert INTO transactions (id, user, service_id, status, rating) Values(3, 'Becky', 2, DONE, 4);
Insert INTO transactions (id, user, service_id, status, rating) Values(4, 'admin', 4, DONE, 1);