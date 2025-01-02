CREATE TABLE caller.operators (
                           id INT PRIMARY KEY,
                           name VARCHAR(100) NOT NULL
);

CREATE TABLE caller.tasks (
                       id INT PRIMARY KEY,
                       name VARCHAR(100) NOT NULL
);

CREATE TABLE caller.calls (
                       id INT PRIMARY KEY,
                       operator_id INT NOT NULL,
                       task_id INT NOT NULL,
                       duration INT NOT NULL, -- in seconds
                       started_at DATETIME NOT NULL,
                       FOREIGN KEY (operator_id) REFERENCES caller.operators(id),
                       FOREIGN KEY (task_id) REFERENCES caller.tasks(id)
);
