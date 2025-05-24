DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS delivery_time;
DROP TABLE IF EXISTS favourite_services:



CREATE TABLE users (
    username VARCHAR PRIMARY KEY,
    name VARCHAR NOT NULL,
    password VARCHAR NOT NULL,
    email VARCHAR NOT NULL,
    admin_power BOOLEAN,
    task_mode BOOLEAN,
    bio VARCHAR,
    profile_photo VARCHAR(255), 
    skills TEXT                  
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    taskname VARCHAR NOT NULL,
    pricing INTEGER NOT NULL,
    delivery_time VARCHAR REFERENCES delivery_time,
    description VARCHAR,
    rating INTEGER,
    Nrating INTEGER,
    category VARCHAR REFERENCES categories,
    user VARCHAR REFERENCES users NOT NULL ON DELETE CASCADE
);

CREATE TABLE favourite_services (
    username VARCHAR REFERENCES users,
    id INTEGER REFERENCES services,
    PRIMARY KEY (username, id)
);

CREATE TABLE comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    text VARCHAR,
    service_id INTEGER REFERENCES services,
    user VARCHAR REFERENCES users NOT NULL,
    replie_to INTEGER REFERENCES comments
);

CREATE TABLE chat (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    text VARCHAR,
    chat_id INTEGER REFERENCES chat,
    sender VARCHAR REFERENCES users NOT NULL ON DELETE CASCADE,
    receiver VARCHAR REFERENCES users NOT NULL ON DELETE CASCADE
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user VARCHAR REFERENCES users NOT NULL,
    service_id INTEGER REFERENCES services NOT NULL,
    status VARCHAR CHECK(status IN ('pending', 'completed', 'cancelled')) NOT NULL,
    rating INTEGER
);

CREATE TABLE categories (
    name VARCHAR PRIMARY KEY
);

CREATE TABLE media (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL REFERENCES services(id),
    file_path TEXT NOT NULL,
    media_type TEXT CHECK(media_type IN ('image', 'video')) NOT NULL
);

CREATE TABLE delivery_time (
    delivery_time VARCHAR PRIMARY KEY
);


--ADMIN USER.  Password can be changed to somethingless secure and quick (!am$9of28FGT-SALT<-funny_joke   OR    12345678)
INSERT INTO users (username, name, password, email, admin_power) VALUES ('admin', 'administrater', '$2y$10$z58xCvcQdCuGP1bM2Hy6zeYFUSJtZSsVMMAuERMGLxJrcE9cAXkf6', 'siteAdmin@fe.up.pt', 1);--12345678

--Regular User.
INSERT INTO users (username, name, password, email, admin_power) VALUES ('Becky', 'Becky Paw', '$2y$10$rafzWJcdAe8Kk9lG6cz2GeyRqLiSyqXYQDhBtWc4hUtGmxiWYY8Wa', 'BeckyP@gmail.com', 0);--!_L0V3_P3T5
INSERT INTO users (username, name, password, email, admin_power) VALUES ('USA', 'MILITARY', '$2y$10$nUJfvOHdsAohUbvg63/BAuBwNK1qGW6vDbnl780n9mvggdH2w9o/2', 'USAMILITARY@Military.USA', 0);--setrgh43876%$

--Services to fill the site a bit
INSERT INTO services (id, pricing, delivery_time, description, rating, user, taskname, category) VALUES (1, 200,'More than a week','Hear hear, do you have a dragon and dont know how to properly train him/her? Dont fear beacuse, for a mere 200 money currency I will train your dragon to be obidient to you. Full refund if you end up unsatisfied and the site allows for refunds. PS: it doesnt',5,'admin', 'How to train your dragon', 'education');
INSERT INTO services (id, pricing, delivery_time, description, rating, user, taskname, category) VALUES (2, 50,'1-2 days','Beginners course for web design. More information after contact.',4,'admin', 'Learnig web design', 'education');
INSERT INTO services (id, pricing, delivery_time, description, rating, user, taskname, category) VALUES (3, 10,'More than a week','Hi, my names Becky and i really LOVE pets. Dogs, cat, snakes, hamsters, you say it, i love it. And thats why I am the PERFECT pick for you to leave your pets with when you need to leave the house. For 10 money currency a day I will take of your precious Kids(not humans tho). Send me a message if you need a Petsitter. Byeee.',4,'Becky', 'Petsitting', 'babysitting');
INSERT INTO services (id, pricing, delivery_time, description, rating, user, taskname, category) VALUES (4, 0,'More than a week','Your country needs you. If you are a young men and unemployed, look no further. Come serve your country under a good salary, a good retirement plan, and a sence of proudness. Join today. PS: Let it be known that lowering our rating helps no one.',1,'USA', 'Military', 'other');

--Delivery times
INSERT INTO delivery_time (delivery_time) VALUES ('Less than a day');
INSERT INTO delivery_time (delivery_time) VALUES ('1-2 days');
INSERT INTO delivery_time (delivery_time) VALUES ('Less than a week');
INSERT INTO delivery_time (delivery_time) VALUES ('More than a week');

--Categories
INSERT INTO categories (name) Values ('education');
INSERT INTO categories (name) Values ('babysitting');
INSERT INTO categories (name) Values ('other');


--Some comments
INSERT INTO comments (id ,comment, service_id, user) Values(1, 'Join the military today', 3, 'USA');
INSERT INTO comments (id ,comment, user, reply_to) Values(2, 'Sorry. Cant leave my babies alone', 'Becky', 1);
INSERT INTO comments (id ,comment, user, reply_to) Values(3, 'If you keep spaming, your account will be terminated', 'admin', 1);
INSERT INTO comments (id ,comment, service_id, user) Values(4, 'Great stuff although, as a professional in the area, i think it is somewhat lackluster', 2, 'Becky');
INSERT INTO comments (id ,comment, service_id, user) Values(5, 'Loved it. My dragon now flyes on command and no longer burn the couch. Please message me if you ever open a people trainig on how to train all your pets', 1, 'Becky');

--Some transactions
Insert INTO transactions (id, user, service_id, status, rating) Values(1, 'USA', 3, 'completed', 4);
Insert INTO transactions (id, user, service_id, status, rating) Values(2, 'Becky', 1, 'completed', 5);
Insert INTO transactions (id, user, service_id, status, rating) Values(3, 'Becky', 2, 'completed', 4);
Insert INTO transactions (id, user, service_id, status, rating) Values(4, 'admin', 4, 'completed', 1);