# Ellie's Map - AreaF5 Technical Test

Technical test in PHP to map all posts and threats in the city of Seattle.

## Table of Contents

- [Requirements](#requirements)
- [Installation and Setup](#installation-and-setup)
- [Docker Environment](#docker-environment)
- [Database Structure](#database-structure)
- [Features](#features)
- [Usage](#usage)
- [Export Examples](#export-examples)

## Requirements

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

## Installation and Setup

### 1. Clone the repository

```bash
git clone https://github.com/Clarisa976/areaf5.git
cd areaf5
```

### 2. Configure environment variables

Create a `.env` file based on `.env.example`:

```bash
cp .env.example .env
```

**Note:** For Docker environment, use the default values:
```env
DB_HOST=mysql
DB_PORT=3306
DB_NAME=bd_areaf5
DB_USER=app
DB_PASS=app
```

### 3. Build and start Docker containers

```bash
docker-compose up -d --build
```

This command will:
- Build the PHP 8.2-FPM image with required extensions (PDO, MySQL, GD, ZIP)
- Install Composer dependencies automatically
- Start MariaDB 10.11 with the database schema
- Configure Nginx 1.27 as web server
- Initialize the database with sample data

### 4. Access the application

Open your browser and navigate to:
```
http://localhost:8080
```

## Docker Environment

### Services

The application runs on three Docker containers:

**PHP** - areaf5-php - - - PHP 8.2-FPM with extensions -
**Nginx** - areaf5-nginx - 8080 - Web server -
**MySQL** - areaf5-mysql - 3307 - MariaDB 10.11 database 

### Useful Docker Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Access PHP container
docker exec -it areaf5-php bash

# Access MySQL database
docker exec -it areaf5-mysql mysql -uapp -papp bd_areaf5

# Rebuild containers
docker-compose up -d --build
```

### Installed PHP Extensions

- PDO & PDO_MySQL (Database connectivity)
- GD (Image processing for PhpSpreadsheet)
- ZIP (Excel file compression)

### Composer Dependencies

- `vlucas/phpdotenv` ^5.6 - Environment variable management
- `phpoffice/phpspreadsheet` ^5.2 - Excel export functionality

## Database Structure

### Entities and Relationships

The database follows a normalized structure with the following entities:

#### **posts**
Represents physical locations/posts in Seattle.

id_posts  TINYINT UNSIGNED  Primary Key
location VARCHAR(255)  Post location name

#### **occupations**
Main entity representing the occupation of each post.

id_occupations  INT UNSIGNED  Primary Key 
post_id  TINYINT UNSIGNED | Foreign Key -> posts 
occupation_type  ENUM (WLF, SERAPHITES, INFECTED_NEST)
character_name  VARCHAR(255)  Character name (for WLF only)
observation TEXT Additional notes

#### **occupation_weapons**
Stores weapons for SERAPHITES occupations (One-to-Many).

id_weapons  INT UNSIGNED  Primary Key
occupation_id  INT UNSIGNED Foreign Key -> occupations
weapon_name  VARCHAR(150) Weapon name

#### **occupation_zombie_type**
Stores zombie types for INFECTED_NEST occupations (One-to-Many).

occupation_id  INT UNSIGNED   Foreign Key -> occupations
zombie_type ENUM(RUNNERS, STALKERS, CLICKERS, BLOATERS, RAT_KING)

**Composite Primary Key:** (occupation_id, zombie_type)

#### **v_post_summary** (VIEW)
Aggregated view that combines all data for easy listing and export.

### Entity Relationship Diagram

```
posts (1) ─────< (N) occupations
                       │
                       ├─────< (N) occupation_weapons
                       │
                       └─────< (N) occupation_zombie_type
```

### Business Rules

1. **One occupation per post**: Each post can only have ONE occupation type at a time
2. **Unique WLF characters**: A WLF character can only be assigned to ONE post across the entire city
3. **Unique Rat King**: Only ONE Rat King can exist in the entire city
4. **Type-specific fields**:
   - WLF -> Requires `character_name`
   - SERAPHITES -> Requires at least one weapon
   - INFECTED_NEST -> Requires at least one zombie type
5. **CASCADE deletion**: Deleting an occupation automatically deletes related weapons and zombie types

## Features

### CRUD Operations

- **Create**: Add new occupations with type-specific validation
- **Read**: View all occupations in a formatted table
- **Update**: Edit existing occupations (preserves business rules)
- **Delete**: Remove occupations with confirmation dialog

### Data Validation

- PHP-based server-side validation (no JavaScript)
- Dynamic form fields based on occupation type
- Real-time field enabling/disabling
- Prevents duplicate occupation types per post
- Ensures WLF character uniqueness across posts
- Validates Rat King uniqueness city-wide

### Export Functionality

- **CSV Export**: Standard comma-separated values format
- **Excel Export**: XLSX format using PhpOffice/PhpSpreadsheet
- Both exports include:
  - Post number and location
  - Occupation type (translated to Spanish)
  - Character name (WLF only)
  - Weapons list (JSON format for SERAPHITES)
  - Zombie types list (JSON format for INFECTED_NEST)
  - Observations

## Usage

### Creating an Occupation

1. Click **"Create new occupation"**
2. Select a post from the dropdown
3. Choose occupation type:
   - **WLF**: Select a character name
   - **SERAPHITES**: Enter weapons (one per line)
   - **INFECTED_NEST**: Select zombie types
4. Add optional observations
5. Click **"Save"**

### Editing an Occupation

1. Click **"Edit"** button on any occupation row
2. Modify the fields (occupation type change will reload the form)
3. Click **"Save changes"**

### Deleting an Occupation

1. Click **"Delete"** button on any occupation row
2. Confirm the deletion in the dialog
3. The occupation and all related data will be removed

### Exporting Data

Click either:
- **"Export CSV"** for CSV format
- **"Export Excel"** for XLSX format

## Export Examples

### CSV Export Example

```csv
"Post Number",Location,"Occupation Type","Character Name",Weapons,"Zombie Types",Observation
2,"Hospital de Seattle",WLF,Mel,NULL,NULL,"Con nausias"
3,"Estación de radio","Nido infectado",NULL,NULL,"[""Stalkers"",""Bloaters""]",
4,Hillcrest,Serafitas,NULL,"[""machete""]",NULL,"grupo de gente feliz"
5,Acuario,WLF,Owen,NULL,NULL,"Busca un regalo para su hijo"
6,"Túneles del metro","Nido infectado",NULL,NULL,"[""Runners"",""Clickers""]","Sitio para pasar el rato"
7,"Barrio de Capitol Hill","Nido infectado",NULL,NULL,"[""Rat King""]",
7,"Barrio de Capitol Hill",Serafitas,NULL,"[""arco"",""lanza"",""piedras""]",NULL,"grupo con mala suerte"
8,Colegio,WLF,Manny,NULL,NULL,"Mi primo"

```



## Project Structure

```
areaf5/
├── config/
│   ├── db.php                    # Database connection
│   └── occupation_functions.php  # Business logic functions
├── docker/
│   ├── mysql/
│   │   └── init.sql             # Database initialization script
│   ├── nginx/
│   │   └── default.conf         # Nginx configuration
│   └── php/
│       └── Dockerfile           # PHP-FPM image definition
├── public/
│   ├── index.php                # Main entry point (listing)
│   ├── occupation_create.php    # Create occupation handler
│   ├── occupation_edit.php      # Edit occupation handler
│   ├── occupation_delete.php    # Delete occupation handler
│   ├── export_csv.php           # CSV export handler
│   └── export_excel.php         # Excel export handler
├── views/
│   ├── list.php                 # Main table view
│   └── occupation_form.php      # Shared form view (create/edit)
├── vendor/                      # Composer dependencies
├── .env                         # Environment configuration
├── .env.example                 # Environment template
├── .dockerignore                # Docker build exclusions
├── .gitignore                   # Git exclusions
├── composer.json                # PHP dependencies
├── composer.lock                # Locked dependency versions
├── docker-compose.yml           # Docker orchestration
└── README.md                    # This file
```

## Technologies Used

- **Backend**: PHP 8.2
- **Database**: MariaDB 10.11
- **Web Server**: Nginx 1.27
- **Containerization**: Docker & Docker Compose
- **Dependencies**:
  - Dotenv (environment management)
  - PhpSpreadsheet (Excel export)
- **Architecture**: MVC-style with separated views and controllers

## Author

**Clarisa976**
- Email: clarisa21h@gmail.com
- GitHub: [@Clarisa976](https://github.com/Clarisa976)

## License

This project is a technical test for AreaF5.
