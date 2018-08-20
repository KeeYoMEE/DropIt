DROP TABLE IF EXISTS home_notes_to_hashtags;
DROP TABLE IF EXISTS home_notes;
DROP TABLE IF EXISTS home_hashtags;
DROP TABLE IF EXISTS users;


CREATE TABLE users (
  id INT(6) SIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(64) NOT NULL,
  hash_key VARCHAR(64) NOT NULL,
  session VARCHAR(100)
);
CREATE TABLE home_notes (
  id INT(16) SIGNED AUTO_INCREMENT PRIMARY KEY,
  created_by INT(6) NOT NULL,
  name VARCHAR(100) NOT NULL UNIQUE,
  ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  content VARCHAR(42000) NOT NULL,
  pinned INT(1),
  last_view TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  archived INT(1),
  FOREIGN KEY (created_by)
        REFERENCES users (id)
        ON DELETE CASCADE
);
CREATE TABLE home_hashtags (
  id INT(16) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);
CREATE TABLE home_notes_to_hashtags (
  id_hashtags INT(16) NOT NULL,
  id_note INT(16) NOT NULL,
  FOREIGN KEY (id_note)
        REFERENCES home_notes (id)
        ON DELETE CASCADE
);
