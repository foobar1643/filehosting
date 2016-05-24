CREATE TABLE files (
    id serial NOT NULL,
    name character varying(255) NOT NULL,
    uploader character varying(35),
    upload_date timestamp DEFAULT CURRENT_TIMESTAMP,
    downloads integer DEFAULT 0,
    auth_token character varying(45),
    CONSTRAINT primary_key PRIMARY KEY (id)
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
    CONSTRAINT table_pkey PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES comments (id),
    FOREIGN KEY (file_id) REFERENCES files (id)
) WITH (
  OIDS=FALSE
);
