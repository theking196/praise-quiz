-- Core schema for Praise Quiz AI backend engine.

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('contestant', 'teacher', 'director', 'admin') NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE age_groups (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  min_age TINYINT UNSIGNED NOT NULL,
  max_age TINYINT UNSIGNED NOT NULL
);

CREATE TABLE categories (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE competitions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  year SMALLINT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

CREATE TABLE contestants (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  age_group_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  competition_id BIGINT UNSIGNED NOT NULL,
  difficulty_level TINYINT UNSIGNED NOT NULL DEFAULT 1,
  current_xp INT UNSIGNED NOT NULL DEFAULT 0,
  stage_reached VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_contestants_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_contestants_age_group FOREIGN KEY (age_group_id) REFERENCES age_groups(id),
  CONSTRAINT fk_contestants_category FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_contestants_competition FOREIGN KEY (competition_id) REFERENCES competitions(id)
);

CREATE TABLE questions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  category_id BIGINT UNSIGNED NOT NULL,
  age_group_id BIGINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  type ENUM('mcq', 'fill_in', 'typed', 'audio', 'essay', 'debate', 'speed_search') NOT NULL,
  options JSON NULL,
  correct_answer TEXT NULL,
  lesson_reference VARCHAR(200) NULL,
  difficulty TINYINT UNSIGNED NOT NULL,
  created_by VARCHAR(120) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_questions_category FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_questions_age_group FOREIGN KEY (age_group_id) REFERENCES age_groups(id)
);

CREATE TABLE question_sets (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  competition_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  age_group_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_question_sets_competition FOREIGN KEY (competition_id) REFERENCES competitions(id),
  CONSTRAINT fk_question_sets_category FOREIGN KEY (category_id) REFERENCES categories(id),
  CONSTRAINT fk_question_sets_age_group FOREIGN KEY (age_group_id) REFERENCES age_groups(id)
);

CREATE TABLE question_set_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  question_set_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  sequence_order SMALLINT UNSIGNED NOT NULL,
  points SMALLINT UNSIGNED NOT NULL,
  CONSTRAINT fk_question_set_items_set FOREIGN KEY (question_set_id) REFERENCES question_sets(id),
  CONSTRAINT fk_question_set_items_question FOREIGN KEY (question_id) REFERENCES questions(id)
);

CREATE TABLE contestant_responses (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  contestant_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  response TEXT NOT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  time_taken DECIMAL(8,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_responses_contestant FOREIGN KEY (contestant_id) REFERENCES contestants(id),
  CONSTRAINT fk_responses_question FOREIGN KEY (question_id) REFERENCES questions(id)
);

CREATE TABLE performance_analytics (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  contestant_id BIGINT UNSIGNED NOT NULL,
  total_score INT UNSIGNED NOT NULL DEFAULT 0,
  average_time DECIMAL(8,2) NOT NULL DEFAULT 0,
  weak_topics JSON NULL,
  badges_earned JSON NULL,
  stage_reached VARCHAR(100) NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_performance_contestant FOREIGN KEY (contestant_id) REFERENCES contestants(id)
);

CREATE TABLE question_history (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  contestant_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NOT NULL,
  asked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_history_contestant FOREIGN KEY (contestant_id) REFERENCES contestants(id),
  CONSTRAINT fk_history_question FOREIGN KEY (question_id) REFERENCES questions(id)
);
