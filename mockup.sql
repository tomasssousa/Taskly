CREATE TABLE users (
    username VARCHAR PRIMARY KEY,
    name VARCHAR NOTNULL,
    password VARCHAR NOTNULL,
    email VARCHAR NOTNULL,
    admin_power BOOLEAN
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY,
    --category VARCHAR REFERENCES categories,
    pricing INTEGER NOTNULL,
    delivery_time VARCHAR,
    description VARCHAR,
    rating INTEGER,
    status VARCHAR, -- talvez seja na transactions
    user VARCHAR REFERENCES users NOTNULL
);

CREATE TABLE service_image (
    id varchar PRIMARY KEY,
    services INTEGER REFERENCES services
);

CREATE TABLE comments (
    id INTEGER PRIMARY KEY,
    comment VARCHAR,
    service_id INTEGER REFERENCES services,
    user VARCHAR REFERENCES users NOTNULL,
    replie_to INTEGER REFERENCES comments
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY,
    user VARCHAR REFERENCES users,
    service_id INTEGER REFERENCES services,
    rating INTEGER, --don't know
);

CREATE TABLE categories (
    category VARCHAR PRIMARY KEY,
    --services INTEGER REFERENCES services
);

CREATE TABLE cate_serv (
    service_id INTEGER REFERENCES services,
    categorie VARCHAR REFERENCES categories
);