USE annamalai_timetable;

-- ============================================================
-- STEP 1: Clear existing data
-- ============================================================
DELETE FROM subjects;
DELETE FROM faculty;
ALTER TABLE subjects AUTO_INCREMENT = 1;
ALTER TABLE faculty AUTO_INCREMENT = 1;

-- ============================================================
-- STEP 2: Insert Faculty (correct designations)
-- ============================================================
INSERT INTO faculty (id, name, designation, specialization, max_hours) VALUES
(1, 'Dr. S. Ponnusamy',   'Professor & Head',   'Computer Science',      18),
(2, 'Dr. Charles',        'Assistant Professor', 'Software Engineering',  16),
(3, 'Dr. Raja',           'Assistant Professor', 'Networks & Security',   16),
(4, 'Dr. Jayalakshmi',    'Guest Faculty',       'Data Science',          12),
(5, 'Umamaheswari',       'Guest Faculty',       'Python & Web',          12),
(6, 'Valli',              'Guest Faculty',       'Database Systems',      12),
(7, 'Dr. Uma',            'Guest Faculty',       'Machine Learning',      12),
(8, 'Dr. Vijayalakshmi',  'Guest Faculty',       'Image Processing',      12);

ALTER TABLE faculty AUTO_INCREMENT = 9;

-- ============================================================
-- STEP 3: Insert UG Subjects (faculty_id = NULL, unassigned)
-- ============================================================

-- UG SEMESTER I (Odd)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Python Programming',           '23UCSCC13', 'UG', 'I', 'Odd', 'Theory', 5, NULL, 'Core I'),
('Python Programming Lab',       '23UCSCP14', 'UG', 'I', 'Odd', 'Lab',    5, NULL, 'Core II - Practical I'),
('Mathematical Foundations - I', '23UMAFE15', 'UG', 'I', 'Odd', 'Theory', 4, NULL, 'Elective I');

-- UG SEMESTER II (Even)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Data Structure and Algorithms',     '23UCSCC23', 'UG', 'II', 'Even', 'Theory', 5, NULL, 'Core III'),
('Data Structure and Algorithms Lab', '23UCSCP24', 'UG', 'II', 'Even', 'Lab',    5, NULL, 'Core IV - Practical II'),
('Mathematical Foundations - II',     '23UMAFE25', 'UG', 'II', 'Even', 'Theory', 4, NULL, 'Elective II');

-- UG SEMESTER III (Odd)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Object Oriented Programming with C++',     '23UCSCC33', 'UG', 'III', 'Odd', 'Theory', 5, NULL, 'Core V'),
('Object Oriented Programming with C++ Lab', '23UCSCP34', 'UG', 'III', 'Odd', 'Lab',    4, NULL, 'Core VI - Practical'),
('Enterprise Resource Planning',             '23UCSCS36', 'UG', 'III', 'Odd', 'Theory', 1, NULL, 'SEC IV'),
('Digital Computer Fundamentals',            '23UCSCS37', 'UG', 'III', 'Odd', 'Theory', 2, NULL, 'SEC V');

-- UG SEMESTER IV (Even)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Java Programming',     '23UCSCC43', 'UG', 'IV', 'Even', 'Theory', 5, NULL, 'Core VII - Industry Module'),
('Java Programming Lab', '23UCSCP44', 'UG', 'IV', 'Even', 'Lab',    3, NULL, 'Core VIII - Practical'),
('PHP Programming',      '23UCSCS46', 'UG', 'IV', 'Even', 'Theory', 2, NULL, 'SEC VI'),
('Computer Networks',    '23UCSCS47', 'UG', 'IV', 'Even', 'Theory', 2, NULL, 'SEC VII');

-- UG SEMESTER V (Odd)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Software Engineering',              '23UCSCC51',   'UG', 'V', 'Odd', 'Theory', 5, NULL, 'Core IX'),
('Database Management System',        '23UCSCC52',   'UG', 'V', 'Odd', 'Theory', 5, NULL, 'Core X'),
('Database Management System Lab',    '23UCSCP53',   'UG', 'V', 'Odd', 'Lab',    5, NULL, 'Core XI - Practical'),
('Operating Systems',                 '23UCSCE55-1', 'UG', 'V', 'Odd', 'Theory', 4, NULL, 'Elective V'),
('Data Mining and Warehousing',       '23UCSCE56-1', 'UG', 'V', 'Odd', 'Theory', 4, NULL, 'Elective VI');

-- UG SEMESTER VI (Even)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Microprocessor and Microcontroller', '23UCSCC61', 'UG', 'VI', 'Even', 'Theory', 6, NULL, 'Core XIII'),
('.NET Programming',                   '23UCSCC62', 'UG', 'VI', 'Even', 'Theory', 6, NULL, 'Core XIV'),
('.NET Programming Lab',               '23UCSCP63', 'UG', 'VI', 'Even', 'Lab',    6, NULL, 'Core XV - Practical'),
('Big Data Analytics',                 '23UCSCF66', 'UG', 'VI', 'Even', 'Theory', 2, NULL, 'Professional Competency');

-- ============================================================
-- PG Subjects
-- ============================================================

-- PG SEMESTER I (Odd)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Analysis & Design of Algorithms', '23PCSCC11',   'PG', 'I', 'Odd', 'Theory', 7, NULL, 'Core I'),
('Python Programming',              '23PCSCC12',   'PG', 'I', 'Odd', 'Theory', 7, NULL, 'Core II'),
('Algorithm and Python Lab',        '23PCSCP13',   'PG', 'I', 'Odd', 'Lab',    6, NULL, 'Core III - Practical'),
('Advance Software Engineering',    '23PCSCE14-1', 'PG', 'I', 'Odd', 'Theory', 5, NULL, 'Elective I'),
('Embedded Systems / IoT',          '23PCSCE15-1', 'PG', 'I', 'Odd', 'Theory', 5, NULL, 'Elective II');

-- PG SEMESTER II (Even)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Data Mining and Warehousing',        '23PCSCC21',   'PG', 'II', 'Even', 'Theory', 6, NULL, 'Core IV'),
('Data Mining & Advanced Java Lab',    '23PCSCP22',   'PG', 'II', 'Even', 'Lab',    6, NULL, 'Core V - Practical'),
('Advanced Java Programming',          '23PCSCC23',   'PG', 'II', 'Even', 'Theory', 6, NULL, 'Core VI'),
('AI & Machine Learning',              '23PCSCE24-1', 'PG', 'II', 'Even', 'Theory', 4, NULL, 'Elective III'),
('Mobile Computing / Blockchain',      '23PCSCE25-1', 'PG', 'II', 'Even', 'Theory', 4, NULL, 'Elective IV'),
('OOP through Java, HTML Basics',      '23PCSCS26',   'PG', 'II', 'Even', 'Theory', 4, NULL, 'SEC I');

-- PG SEMESTER III (Odd)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Digital Image Processing',                  '23PCSCC31', 'PG', 'III', 'Odd', 'Theory', 6, NULL, 'Core IX'),
('Cloud Computing',                           '23PCSCC32', 'PG', 'III', 'Odd', 'Theory', 6, NULL, 'Core X'),
('Network Security and Cryptography',         '23PCSCC33', 'PG', 'III', 'Odd', 'Theory', 6, NULL, 'Core XI'),
('Data Science & Analytics',                  '23PCSCC34', 'PG', 'III', 'Odd', 'Theory', 6, NULL, 'Core XII'),
('Digital Image Processing Lab (MATLAB)',     '23PCSCE35', 'PG', 'III', 'Odd', 'Lab',    3, NULL, 'Elective V - Practical'),
('Cloud Computing Lab',                       '23PCSCS36', 'PG', 'III', 'Odd', 'Lab',    3, NULL, 'SEC II - Practical');

-- PG SEMESTER IV (Even)
INSERT INTO subjects (name, code, programme, semester, sem_type, subject_type, hours_per_week, faculty_id, notes) VALUES
('Data Analytics Lab',                '23PCSCP41',   'PG', 'IV', 'Even', 'Lab',    6, NULL, 'Core XI - Practical'),
('Web Application Dev & Hosting',     '23PCSCP42',   'PG', 'IV', 'Even', 'Lab',    6, NULL, 'Core XII - Practical'),
('Introduction to Robotics / VR',     '23PCSCE44-1', 'PG', 'IV', 'Even', 'Theory', 4, NULL, 'Elective VI'),
('Soft Skills',                       '23PCSCS45',   'PG', 'IV', 'Even', 'Theory', 4, NULL, 'SEC - Professional Competency');

-- ============================================================
-- Verify
-- ============================================================
SELECT 'Faculty count:' AS info, COUNT(*) AS total FROM faculty;
SELECT 'Subject count:' AS info, COUNT(*) AS total FROM subjects;
SELECT 'Unassigned subjects:' AS info, COUNT(*) AS total FROM subjects WHERE faculty_id IS NULL;
