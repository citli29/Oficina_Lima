import csv

MAKES_FILE = "car_makes.csv"
MODELS_FILE = "car_models.csv"
OUTPUT_FILE = "seed_cars_fast.sql"

BATCH_SIZE = 500  # tweak this (500–2000 is usually ideal)


def sql_escape(value):
    return value.replace("'", "''")


def write_bulk_insert(f, table, columns, rows):
    if not rows:
        return

    f.write(f"INSERT INTO {table} ({', '.join(columns)}) VALUES\n")
    f.write(",\n".join(rows))
    f.write(";\n\n")


def process_makes(f):
    batch = []

    with open(MAKES_FILE, newline="", encoding="utf-8") as file:
        reader = csv.reader(file, delimiter=";")

        for row in reader:
            if row[0] == "id":
                continue

            make_id = row[0]
            name = sql_escape(row[1])

            batch.append(f"({make_id}, '{name}')")

            if len(batch) >= BATCH_SIZE:
                write_bulk_insert(f, "marcas", ["id", "nome"], batch)
                batch = []

    write_bulk_insert(f, "marcas", ["id", "nome"], batch)


def process_models(f):
    batch = []

    with open(MODELS_FILE, newline="", encoding="utf-8") as file:
        reader = csv.reader(file, delimiter=";")

        for row in reader:
            if row[0] == "id":
                continue

            model_id = row[0]
            name = sql_escape(row[1])
            make_id = row[2]

            batch.append(f"({model_id}, '{name}', {make_id})")

            if len(batch) >= BATCH_SIZE:
                write_bulk_insert(
                    f,
                    "modelos",
                    ["id", "nome", "marca_id"],
                    batch
                )
                batch = []

    write_bulk_insert(f, "modelos", ["id", "nome", "marca_id"], batch)


def main():
    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        f.write("-- Fast bulk seed file\n\n")
        f.write("BEGIN TRANSACTION;\n\n")

        f.write("-- Marcas\n")
        process_makes(f)

        f.write("-- Modelos\n")
        process_models(f)

        f.write("COMMIT;\n")

    print(f"Generated {OUTPUT_FILE}")


if __name__ == "__main__":
    main()
