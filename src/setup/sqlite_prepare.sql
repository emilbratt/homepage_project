CREATE TABLE blog_status
(
    id_status       INTEGER PRIMARY KEY AUTOINCREMENT , -- the blog id contains one greybox == one post on website
    desc_status     VARCHAR(128) NOT NULL,              -- identifies status type
    alias           VARCHAR(128) NOT NULL               -- to show on a select list or something

);

-- INSERT VALID BLOG STATUS TYPE DATA
INSERT INTO blog_status
    (desc_status, alias)
VALUES
    ('Not posted','Deactivate Blogpost'),
    ('Posted','Activate Blogpost')
;

CREATE TABLE blog
(
    id_blog         INTEGER PRIMARY KEY AUTOINCREMENT   NOT NULL,   -- the blog id contains one greybox == one post on website
    description     VARCHAR(256) UNIQUE                 NOT NULL,   -- can be a title or simple text describing the post (will not show on webpage)
    tags            VARCHAR(256),                                   -- example: python,programming,photographing
    date_started    DATE DEFAULT CURRENT_DATE           NOT NULL,   -- will not be changed once created, other datestamps will take care of that
    time_started    TIME DEFAULT CURRENT_TIME           NOT NULL,   -- will not be changed once created, other timestamps will take care of that
    date_posted     DATE,                                           -- if no value in date_mod, this will be the current (newest) date
    time_posted     TIME,                                           -- if no value in time_mod, this will be the current (newest) time
    date_mod        DATE,                                           -- if I want to sort by newest post by modification, this datestamp will make that possible
    time_mod        TIME,                                           -- if I want to sort by newest post by modification, this timestamp will make that possible
    id_status       TINYINT UNSIGNED DEFAULT '1'        NOT NULL,

    /*  STATUS CODES
        0 = post under developement / not finished -> de-activated (default when created)
        1 = post on website blog page -> activated
        NOT IN USE 2 = show on preview page -> preview.php
    */

    CONSTRAINT id_status_blog
        FOREIGN KEY (id_status)
            REFERENCES blog_status (id_status)

);


CREATE TABLE blog_content_type
(
    id_type         INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, -- the blog id contains one greybox == one post on website
    desc_type       VARCHAR(128) NOT NULL,                      -- identifies content type
    alias           VARCHAR(128) NOT NULL                       -- to show on a select list or something

);

-- INSERT VALID BLOG CONTENT TYPE DATA
INSERT INTO blog_content_type
    (desc_type, alias)
VALUES
    ('main_title_center','Centered Title'),                       -- -> text
    ('main_title_left','Left Title'),                             -- -> text
    ('main_title_right','Right Title'),                           -- -> text
    ('body_title','Sub Header'),                                  -- -> text
    ('body_text','Textfield'),                                    -- -> text
    ('body_image_center','Centered Image'),                       -- -> img_name + img_caption
    ('body_image_left','Left Image'),                             -- -> img_name + img_caption
    ('body_image_right','Right Image'),                           -- -> img_name + img_caption
    ('body_image_center_caption','Centered Image with Caption'),  -- -> img_name + img_caption + img_caption
    ('body_image_left_caption','Left Image with Caption'),        -- -> img_name + img_caption + img_caption
    ('body_image_right_caption','Right Image with Caption')       -- -> img_name + img_caption + img_caption
;

CREATE TABLE blog_content
(
    id_blog         SMALLINT            NOT NULL,   -- the id_blog contains one greybox on website
    id_type         SMALLINT            NOT NULL,   -- the id_type contains one element in a blog/greybox
    content_number  SMALLINT UNSIGNED   NOT NULL,   -- follows designated enumeration (multiple numers) for each blog id (post)
    main_title      VARCHAR(256),                   -- field for titl
    body_title      VARCHAR(256),                   -- field for body title
    body_text       VARCHAR(4096),                  -- field for body_text
    img_name        VARCHAR(256),                   -- example set-0001
    img_folder      VARCHAR(256),                   -- example nature (assumes image resides in ./original/nature/)
    img_caption     VARCHAR(1024),                  -- text that belongs to the image

    PRIMARY KEY
        (id_blog, content_number), -- indexing and avoiding duplicate of same id_blog and content_number

    CONSTRAINT id_blog_content
        FOREIGN KEY (id_blog)
            REFERENCES blog (id_blog)
                ON DELETE CASCADE,

    CONSTRAINT id_type_blog_content
        FOREIGN KEY (id_type)
            REFERENCES blog_content_type (id_type)

);


CREATE TABLE IF NOT EXISTS front_page  -- short description about the page or about you
(
    id_content      TINYINT UNSIGNED    NOT NULL,  -- should really not exceed a 1 digit amount of rows, therefor tiny integer
    body_title      VARCHAR(64)         NOT NULL,  -- body-title on left side of front page
    body_text       VARCHAR(256)        NOT NULL,  -- body-text on left side of front page

    PRIMARY KEY (id_content, body_title)
);

CREATE TABLE user_data -- for several fields on front page as well as other things
(
    full_name       VARCHAR(64)     NOT NULL,   -- shows on top of front page
    profile_pic     VARCHAR(128)    NOT NULL,   -- path to uploaded image
    email           VARCHAR(64)     NOT NULL    -- email in Contact field
);



CREATE TABLE IF NOT EXISTS social_networks -- for menu icons on the bottom navigation bar
(
    name    VARCHAR(40) PRIMARY KEY NOT NULL,   -- example github
    url     VARCHAR(512)                        -- example: https://github.com/mygithub
);


CREATE TABLE IF NOT EXISTS image_org
(
    id_image        INTEGER PRIMARY KEY,
    file_name       VARCHAR(256)        NOT NULL, -- example: my_photo.tiff
    category        VARCHAR(256)        NOT NULL, -- example: nature
    aspect          VARCHAR(64)         NOT NULL, -- example: portrait
    width           SMALLINT UNSIGNED   NOT NULL, -- example: 1920
    height          SMALLINT UNSIGNED   NOT NULL, -- example: 1080
    org_target      VARCHAR(512)        NOT NULL, -- example: ./orig/path/photo.tiff
    date_insert     DATE DEFAULT CURRENT_DATE,
    time_insert     TIME DEFAULT CURRENT_TIME
);

CREATE TABLE IF NOT EXISTS image_resize
(
    id_image        INTEGER NOT NULL,
    file_name       VARCHAR(256)        NOT NULL, -- example: my_photo.png
    format          CHAR(5)             NOT NULL, -- example: png
    resize_target   VARCHAR(512)        NOT NULL, -- example: ./resized/path/photo.png
    long_edge       SMALLINT UNSIGNED   NOT NULL, -- example: 1600 (longest side in pixels)
    date_insert     DATE DEFAULT CURRENT_DATE,
    time_insert     TIME DEFAULT CURRENT_TIME,

    FOREIGN KEY (id_image)
        REFERENCES image_org (id_image)
            ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS log_level
(
    id_log_level        INTEGER PRIMARY KEY AUTOINCREMENT    NOT NULL,
    level_description   VARCHAR(32) NOT NULL
);

-- INSERT VALID LOG LEVELS
INSERT INTO log_level
    (level_description)
VALUES
    ('info'),
    ('Warning'),
    ('Debug'),
    ('Error'),
    ('Fatal')
;

CREATE TABLE IF NOT EXISTS logging
(
    id_log          INTEGER PRIMARY KEY,
    date_log        DATE DEFAULT CURRENT_DATE NOT NULL,
    time_log        TIME DEFAULT CURRENT_TIME NOT NULL,
    subject         VARCHAR(255),
    message         VARCHAR(255) NOT NULL,
    id_log_level    INTEGER DEFAULT '0' NOT NULL,

    /*  ERROR LOG LEVELS -> id_log_level
        1 = info (nothing wrong happened)
        2 = Warning
        3 = Debug
        4 = Error
        5 = Fatal
    */

    CONSTRAINT id_log_level
        FOREIGN KEY (id_log_level)
            REFERENCES log_level (id_log_level)

);




-- INSERT DUMMY DATA (REMOVE WHEN PRODUCTION READY)
INSERT INTO blog
    (description, tags)
VALUES
(
    'Blogpost 1',
    'programming,web developement, computers, python'
),
(
    'Blogpost 2',
    'programming,web developement, computers, php'
),
(
    'Blogpost 3',
    'programming,web developement, computers, php'
);

-- INSERT DUMMY DATA (REMOVE WHEN PRODUCTION READY)
INSERT INTO blog_content
    (id_blog,id_type, content_number, main_title)
VALUES
    ('1','2','1','Python Stuff'),
    ('1','3','2','__date__'),
    ('2','2','1','Programmin Stuf'),
    ('2','3','2','__date__'),
    ('3','2','1','Learning PHP'),
    ('3','3','2','__date__')

;

-- INSERT DUMMY DATA (REMOVE WHEN PRODUCTION READY)
INSERT INTO blog_content
    (id_blog,id_type, content_number, body_title)
VALUES
    ('1','4','3','Some random body title, jihaa'),
    ('2','4','3','Some random body title, jihaa')
;
INSERT INTO blog_content
    (id_blog,id_type, content_number, body_text)
VALUES
    ('1','5','4','Some random body text 1'),
    ('1','5','5','Some random body text 2')
;

UPDATE blog
SET id_status = '2'
WHERE id_blog < '3';
