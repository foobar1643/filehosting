CREATE TABLE files (
    id serial NOT NULL,
    name character varying(256) NOT NULL,
    uploader character varying(35),
    upload_date timestamp DEFAULT CURRENT_TIMESTAMP,
    filesize integer NOT NULL,
    downloads integer DEFAULT 0,
    auth_token character varying(45),
    CONSTRAINT files_pkey PRIMARY KEY (id)
) WITH (
  OIDS=FALSE
);

CREATE TABLE comments (
    id serial NOT NULL,
    parent_id integer,
    file_id integer NOT NULL,
    author character varying(35) NOT NULL,
    date_posted timestamp NOT NULL,
    comment_text text NOT NULL,
    parent_path character varying(255),
    CONSTRAINT comments_pkey PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES comments (id),
    FOREIGN KEY (file_id) REFERENCES files (id)
) WITH (
  OIDS=FALSE
);

CREATE INDEX upload_date_index ON files (upload_date);
CREATE INDEX downloads_index ON files (downloads);
CREATE INDEX comments_matpath_index ON comments (parent_path);