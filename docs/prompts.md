├── api
│   ├── app
│   ├── public
│   ├── routes
│   └── test
├── composer.json
├── composer.lock
├── database
│   ├── applied_product.php
│   ├── client.php
│   ├── connection.db.php
│   ├── database.db
│   ├── database.sql
│   ├── make.php
│   ├── model.php
│   ├── product.php
│   ├── product_type.php
│   ├── schedule.php
│   ├── service_applied_product.php
│   ├── service.php
│   ├── service_user_time.php
│   ├── user.php
│   ├── user_registry.php
│   ├── user_time.php
│   └── user_type.php
├── docs
│   ├── api.docs.md
│   ├── api_responses.md
│   ├── schema.md
│   ├── TaskList.md
│   ├── tasks.md
│   └── TestCases.md
├── helper
├── imported_car_info
│   ├── car_makes.csv
│   ├── car_models.csv
│   ├── README.md
│   ├── seed_cars_fast.sql
│   └── sql_convert.py
├── README.md
├── tests
│   ├── CarTest.php
│   ├── ClientTest.php
│   ├── MakeTest.php
│   ├── ModelTest.php
│   ├── ProductTest.php
│   ├── ProductTypeTest.php
│   ├── ScheduleTest.php
│   ├── ServiceAppliedProductTest.php
│   ├── ServiceTest.php
│   ├── ServiceUserTimeTest.php
│   ├── testdatabase.db
│   ├── TestDatabase.php
│   ├── UserTest.php
│   └── UserTypeTest.php
├── utils
│   ├── session.php
│   └── util.php
└── vendor
    ├── autoload.php
    ├── bin
    ├── composer
    ├── guzzlehttp
    ├── myclabs
    ├── nikic
    ├── phar-io
    ├── phpunit
    ├── psr
    ├── ralouphie
    ├── sebastian
    ├── staabm
    ├── symfony
    └── thesee

before those changes: ServicesShow.jsx:1103 Uncaught ReferenceError: appliedProducts is not defined

    at ServicesShow (ServicesShow.jsx:1103:14), what i have is: import { useEffect, useState } from "react";

import { useNavigate, useParams } from "react-router-dom";

import api from "../../api/axios";



import AppliedProductsTable from "./AppliedProductsTable";

import UserTimesTable from "./UserTimesTable";



import "./ServicesShow.css";



export default function ServicesShow() {



	const { id } = useParams();

	const navigate = useNavigate();



	const [service, setService] = useState(null);

	const [editing, setEditing] = useState(null);



	const [isEditing, setIsEditing] = useState(false);



	const [cars, setCars] = useState([]);

	const [clients, setClients] = useState([]);

	const [makes, setMakes] = useState([]);

	const [models, setModels] = useState([]);



	const [creatingClient, setCreatingClient] = useState(false);

	const [creatingCar, setCreatingCar] = useState(false);

	const [creatingMake, setCreatingMake] = useState(false);

	const [creatingModel, setCreatingModel] = useState(false);



	const emptyClient = {

		name: "",

		phone: "",

		address: "",

		email: "",

		zip_code: "",

		tax_nr: ""

	};



	const emptyCar = {

		plate: "",

		make_id: "",

		model_id: "",

		month: "",

		year: "",

		cc: "",

		engine_code: "",

		color_code: "",

		chassi_nr: ""

	};



	const [newClient, setNewClient] = useState(emptyClient);

	const [newCar, setNewCar] = useState(emptyCar);



	const [newMakeName, setNewMakeName] = useState("");

	const [newModelName, setNewModelName] = useState("");



	const [message, setMessage] = useState({

		type: "",

		text: ""

	});

		function showMessage(type, text) {



		setMessage({

			type,

			text

		});



		setTimeout(() => {



			setMessage({

				type: "",

				text: ""

			});



		}, 4000);



	}



	function handleApiError(err) {



		if (err.response?.data?.error) {



			showMessage(

				"error",

				err.response.data.error

			);



		}

		else {



			showMessage(

				"error",

				"Something went wrong."

			);



		}



		console.error(err);



	}

	useEffect(() => {



		loadService();

		loadClients();

		loadCars();

		loadMakes();



	}, [id]);



	useEffect(() => {



		if (editing?.make_id) {



			loadModels(editing.make_id);



		}

		else {



			setModels([]);



		}



	}, [editing?.make_id]);

	async function loadService() {



	try {



		const res = await api.get(`/services/${id}`);



		setService(res.data.service);



		setEditing({

			...res.data.service,

			make_id: res.data.service.car_make_id || "",

			model_id: res.data.service.car_model_id || ""

		});



	}

	catch (err) {



		handleApiError(err);



	}



}

async function loadCars() {



	try {



		const res = await api.get("/cars");



		setCars(res.data.car_list || []);



	}

	catch {



		setCars([]);



	}



}

async function loadClients() {



	try {



		const res = await api.get("/clients");



		setClients(res.data.client_list || []);



	}

	catch {



		setClients([]);



	}



}

async function loadMakes() {



	try {



		const res = await api.get("/makes");



		setMakes(res.data.make_list || []);



	}

	catch {



		setMakes([]);



	}



}

async function loadModels(make_id) {



	if (!make_id) {



		setModels([]);

		return;



	}



	try {



		const res = await api.get("/models", {

			params: {

				make_id

			}

		});



		setModels(res.data.model_list || []);



	}

	catch {



		setModels([]);



	}



} 

const selectedClient = clients.find(

	client => client.id === Number(editing?.client_id)

);

	const selectedCar = cars.find(

	car => car.id === Number(editing?.car_id)

);

	function beginEdit() {



	setIsEditing(true);



}



function cancelEdit() {



	setIsEditing(false);



	setCreatingClient(false);

	setCreatingCar(false);

	setCreatingMake(false);

	setCreatingModel(false);



	setNewClient(emptyClient);

	setNewCar(emptyCar);



	loadService();



}



function updateEdit(e) {



	const { name, value } = e.target;



	if (name === "client_id" && value === "new") {



		setCreatingClient(true);

		return;



	}



	if (name === "car_id" && value === "new") {



		setCreatingCar(true);

		return;



	}



	setEditing(prev => {



		const updated = {

			...prev,

			[name]: value

		};



		if (name === "car_id") {



			if (value !== "") {



				updated.make_id = "";

				updated.model_id = "";



			}



		}



		if (name === "make_id") {



			updated.model_id = "";

			loadModels(value);



		}



		return updated;



	});



}



function updateNewClient(e) {



	const { name, value } = e.target;



	setNewClient(prev => ({

		...prev,

		[name]: value

	}));



}



function updateNewCar(e) {



	const { name, value } = e.target;



	setNewCar(prev => {



		const updated = {

			...prev,

			[name]: value

		};



		if (name === "make_id") {



			updated.model_id = "";

			loadModels(value);



		}



		return updated;



	});



}

async function createMake() {



	if (!newMakeName.trim()) {



		showMessage(

			"error",

			"Make name is required."

		);



		return;



	}



	try {



		const res = await api.post("/makes", {

			name: newMakeName

		});



		const make = res.data.make;



		setMakes(prev => [

			...prev,

			make

		]);



		setNewCar(prev => ({

			...prev,

			make_id: make.id,

			model_id: ""

		}));



		setCreatingMake(false);

		setNewMakeName("");



		showMessage(

			"success",

			"Make created successfully."

		);



	}

	catch (err) {



		handleApiError(err);



	}



}



async function createModel() {



	if (!newCar.make_id) {



		showMessage(

			"error",

			"Select a make first."

		);



		return;



	}



	if (!newModelName.trim()) {



		showMessage(

			"error",

			"Model name is required."

		);



		return;



	}



	try {



		const res = await api.post("/models", {

			name: newModelName,

			make_id: newCar.make_id

		});



		const model = res.data.model;



		setModels(prev => [

			...prev,

			model

		]);



		setNewCar(prev => ({

			...prev,

			model_id: model.id

		}));



		setCreatingModel(false);

		setNewModelName("");



		showMessage(

			"success",

			"Model created successfully."

		);



	}

	catch (err) {



		handleApiError(err);



	}



}



async function createClient() {



	if (!newClient.name.trim()) {



		showMessage(

			"error",

			"Client name is required."

		);



		return;



	}



	if (!newClient.phone.trim()) {



		showMessage(

			"error",

			"Phone is required."

		);



		return;



	}



	try {



		const data = Object.fromEntries(

			Object.entries(newClient)

				.filter(([_, value]) => value !== "")

		);



		const res = await api.post(

			"/clients",

			data

		);



		const client = res.data.client;



		setClients(prev => [

			...prev,

			client

		]);



		setEditing(prev => ({

			...prev,

			client_id: client.id

		}));



		setCreatingClient(false);

		setNewClient(emptyClient);



		showMessage(

			"success",

			"Client created successfully."

		);



	}

	catch (err) {



		handleApiError(err);



	}



}



async function createCar() {



	if (!newCar.plate.trim()) {



		showMessage(

			"error",

			"Plate is required."

		);



		return;



	}



	if (!newCar.make_id) {



		showMessage(

			"error",

			"Please select a make."

		);



		return;



	}



	try {



		const data = Object.fromEntries(

			Object.entries(newCar)

				.filter(([_, value]) => value !== "")

		);



		const res = await api.post(

			"/cars",

			data

		);



		const car = res.data.car;



		setCars(prev => [

			...prev,

			car

		]);



		setEditing(prev => ({

			...prev,

			car_id: car.id,

			make_id: "",

			model_id: ""

		}));



		setCreatingCar(false);

		setCreatingMake(false);

		setCreatingModel(false);



		setNewCar(emptyCar);



		showMessage(

			"success",

			"Car created successfully."

		);



	}

	catch (err) {



		handleApiError(err);



	}



}

async function saveService() {



	try {



		const data = {

			checkin: editing.checkin,

			checkout: editing.checkout,

			kms: editing.kms,

			malfunction: editing.malfunction,

			service: editing.service

		};



		if (editing.client_id) {



			data.client_id = editing.client_id;



		}



		if (editing.car_id) {



			data.car_id = editing.car_id;



		}

		else if (editing.model_id) {



			data.model_id = editing.model_id;



		}



		await api.put(

			`/services/${id}`,

			data

		);



		showMessage(

			"success",

			"Service updated successfully."

		);



		setIsEditing(false);



		loadService();



	}

	catch (err) {



		handleApiError(err);



	}



}



async function deleteService() {



	if (!window.confirm("Delete this service?"))

		return;



	try {



		await api.delete(`/services/${id}`);



		showMessage(

			"success",

			"Service deleted successfully."

		);



		navigate("/services");



	}

	catch (err) {



		handleApiError(err);



	}



}



if (!editing) {



	return (

		<div className="container">

			Loading...

		</div>

	);



}

return (



	<div className="container">



		<h1>Service #{id}</h1>



		{message.text && (

			<div className={`api-message ${message.type}`}>

				{message.text}

			</div>

		)}



		<div className="details-card">



			<div className="details-grid">



				{/* ================= CLIENT ================= */}



				<div className="field">



					<label>Client</label>



					{isEditing ? (



						!creatingClient ? (



							<>



								<select

									name="client_id"

									value={editing.client_id || ""}

									onChange={updateEdit}

								>



									<option value="">

										No client

									</option>



									{clients.map(client => (



										<option

											key={client.id}

											value={client.id}

										>

											{client.name} ({client.phone})

										</option>



									))}



									<option value="new">

										+ Create new client

									</option>



								</select>



								{selectedClient && (



									<div className="info-box">



										<div>

											<strong>Name:</strong> {selectedClient.name}

										</div>



										<div>

											<strong>Phone:</strong> {selectedClient.phone}

										</div>



										<div>

											<strong>Email:</strong> {selectedClient.email || "-"}

										</div>



										<div>

											<strong>Address:</strong> {selectedClient.address || "-"}

										</div>



										<div>

											<strong>ZIP:</strong> {selectedClient.zip_code || "-"}

										</div>



										<div>

											<strong>Tax:</strong> {selectedClient.tax_nr || "-"}

										</div>



									</div>



								)}



							</>



						) : (



							<div className="inline-create">



								<input

									name="name"

									placeholder="Client Name"

									value={newClient.name}

									onChange={updateNewClient}

								/>



								<input

									name="phone"

									placeholder="Phone"

									value={newClient.phone}

									onChange={updateNewClient}

								/>



								<input

									name="email"

									placeholder="Email"

									value={newClient.email}

									onChange={updateNewClient}

								/>



								<input

									name="address"

									placeholder="Address"

									value={newClient.address}

									onChange={updateNewClient}

								/>



								<input

									name="zip_code"

									placeholder="ZIP"

									value={newClient.zip_code}

									onChange={updateNewClient}

								/>



								<input

									name="tax_nr"

									placeholder="Tax Number"

									value={newClient.tax_nr}

									onChange={updateNewClient}

								/>



								<div className="create-buttons">



									<button onClick={createClient}>

										Add

									</button>



									<button

										onClick={() => {



											setCreatingClient(false);

											setNewClient(emptyClient);



										}}

									>

										Cancel

									</button>



								</div>



							</div>



						)



					) : (



						<>



							<input

								readOnly

								value={

									service.client_name

										? `${service.client_name} (${service.client_phone})`

										: "-"

								}

							/>



							{selectedClient && (



								<div className="info-box">



									<div>

										<strong>Name:</strong> {selectedClient.name}

									</div>



									<div>

										<strong>Phone:</strong> {selectedClient.phone}

									</div>



									<div>

										<strong>Email:</strong> {selectedClient.email || "-"}

									</div>



									<div>

										<strong>Address:</strong> {selectedClient.address || "-"}

									</div>



									<div>

										<strong>ZIP:</strong> {selectedClient.zip_code || "-"}

									</div>



									<div>

										<strong>Tax:</strong> {selectedClient.tax_nr || "-"}

									</div>



								</div>



							)}



						</>



					)}



				</div>



				{/* ================= SERVICE INFO ================= */}



				<div className="field field-full">



					<label>Check In</label>



					<input

						type="date"

						name="checkin"

						value={editing.checkin || ""}

						onChange={updateEdit}

						readOnly={!isEditing}

					/>



				</div>



				<div className="field">



					<label>Check Out</label>



					<input

						type="date"

						name="checkout"

						value={editing.checkout || ""}

						onChange={updateEdit}

						readOnly={!isEditing}

					/>



				</div>



				<div className="field">



					<label>Kilometers</label>



					<input

						type="number"

						name="kms"

						value={editing.kms || ""}

						onChange={updateEdit}

						readOnly={!isEditing}

					/>



				</div>



				<div className="field field-full">



					<label>Malfunction</label>



					<textarea

						name="malfunction"

						value={editing.malfunction || ""}

						onChange={updateEdit}

						readOnly={!isEditing}

					/>



				</div>



				<div className="field field-full">



					<label>Service Performed</label>



					<textarea

						name="service"

						value={editing.service || ""}

						onChange={updateEdit}

						readOnly={!isEditing}

					/>



				</div>



				{/* CAR SECTION STARTS HERE */}

				{/* ================= CAR ================= */}



				<div className="details-section">



					<h2>Car Information</h2>



					<div className="details-grid">



						<div className="field">

							<label>Car</label>



							{isEditing ? (



								<select

									name="car_id"

									value={editing.car_id || ""}

									onChange={updateEdit}

								>



									<option value="">

										Select Car

									</option>



									{cars.map(car => (



										<option

											key={car.id}

											value={car.id}

										>

											{car.plate} - {car.make_name} {car.model_name}

										</option>



									))}



								</select>



							) : (



								<input

									readOnly

									value={

										service.car_plate

											? `${service.car_plate} - ${service.car_make_name} ${service.car_model_name}`

											: "-"

									}

								/>



							)}



						</div>



						<div className="field">

							<label>Plate</label>



							<input

								readOnly

								value={service.car_plate || "-"}

							/>



						</div>



						<div className="field">

							<label>Make</label>



							<input

								readOnly

								value={service.car_make_name || "-"}

							/>



						</div>



						<div className="field">

							<label>Model</label>



							<input

								readOnly

								value={service.car_model_name || "-"}

							/>



						</div>



						<div className="field">

							<label>Year</label>



							<input

								readOnly

								value={service.car_year || "-"}

							/>



						</div>



						<div className="field">

							<label>Month</label>



							<input

								readOnly

								value={service.car_month || "-"}

							/>



						</div>



						<div className="field">

							<label>CC</label>



							<input

								readOnly

								value={service.car_cc || "-"}

							/>



						</div>



						<div className="field">

							<label>Engine Code</label>



							<input

								readOnly

								value={service.car_engine_code || "-"}

							/>



						</div>



						<div className="field">

							<label>Color Code</label>



							<input

								readOnly

								value={service.car_color_code || "-"}

							/>



						</div>



						<div className="field">

							<label>Chassis</label>



							<input

								readOnly

								value={service.car_chassi_nr || "-"}

							/>



						</div>



					</div>



				</div>

				{/* ================= APPLIED PRODUCTS ================= */}



				<div className="details-section">



					<div className="section-header">



						<h2>Applied Products</h2>



						<button

							type="button"

							onClick={() => setAddingProduct(true)}

						>

							Add Product

						</button>



					</div>



					<AppliedProductsTable

						items={appliedProducts}

						isEditing={isEditing}

						onAdd={() => setAddingProduct(true)}

						onEdit={(product) => {



							setEditingProduct(product);

							setAddingProduct(true);



						}}

						onDelete={deleteAppliedProduct}

						onRefresh={loadAppliedProducts}

					/>



					{addingProduct && (



						<div className="inline-create">



							<div className="details-grid">



								<div className="field">



									<label>Product</label>



									<select

										name="product_id"

										value={editingProduct.product_id || ""}

										onChange={updateEditingProduct}

									>



										<option value="">

											Select Product

										</option>



										{products.map(product => (



											<option

												key={product.id}

												value={product.id}

											>

												{product.name}

											</option>



										))}



									</select>



								</div>



								<div className="field">



									<label>Quantity</label>



									<input

										type="number"

										name="quantity"

										min="1"

										value={editingProduct.quantity || 1}

										onChange={updateEditingProduct}

									/>



								</div>



								<div className="field">



									<label>Applied</label>



									<select

										name="is_applied"

										value={editingProduct.is_applied}

										onChange={updateEditingProduct}

									>



										<option value={1}>

											Yes

										</option>



										<option value={0}>

											No

										</option>



									</select>



								</div>



							</div>



							<div className="create-buttons">



								<button onClick={saveAppliedProduct}>

									Save

								</button>



								<button

									type="button"

									onClick={() => {



										setAddingProduct(false);



										setEditingProduct({

											id: null,

											product_id: "",

											quantity: 1,

											is_applied: 1

										});



									}}

								>

									Cancel

								</button>



							</div>



						</div>



					)}



				</div>

					<div className="field">



						<label>Car</label>



						{isEditing ? (



							<select

								name="car_id"

								value={editing.car_id || ""}

								onChange={updateEdit}

							>



								<option value="">

									No car

								</option>



								{cars.map(car => (



									<option

										key={car.id}

										value={car.id}

									>

										{car.plate} - {car.make_name} {car.model_name}

									</option>



								))}



							</select>



						) : (



							<input

								readOnly

								value={

									service.car_plate

										? `${service.car_plate} (${service.car_make_name} ${service.car_model_name})`

										: "-"

								}

							/>



						)}



						<div className="info-box">



							<div>

								<strong>Plate:</strong> {service.car_plate || "-"}

							</div>



							<div>

								<strong>Make:</strong> {service.car_make_name || "-"}

							</div>



							<div>

								<strong>Model:</strong> {service.car_model_name || "-"}

							</div>



							<div>

								<strong>Year:</strong> {service.car_year || "-"}

							</div>



							<div>

								<strong>Month:</strong> {service.car_month || "-"}

							</div>



							<div>

								<strong>CC:</strong> {service.car_cc || "-"}

							</div>



							<div>

								<strong>Engine Code:</strong> {service.car_engine_code || "-"}

							</div>



							<div>

								<strong>Color Code:</strong> {service.car_color_code || "-"}

							</div>



							<div>

								<strong>Chassis:</strong> {service.car_chassi_nr || "-"}

							</div>



						</div>



					</div>



				</div>



				<h2>Applied Products</h2>



				<AppliedProductsTable

					serviceId={id}

					isEditing={isEditing}

					showMessage={showMessage}

					handleApiError={handleApiError}

				/>



				<h2>User Times</h2>



				<UserTimesTable

					serviceId={id}

					isEditing={isEditing}

					showMessage={showMessage}

					handleApiError={handleApiError}

				/>



				<div className="details-actions">



					{!isEditing ? (



						<>



							<button onClick={beginEdit}>

								Edit

							</button>



							<button

								className="delete-btn"

								onClick={deleteService}

							>

								Delete

							</button>



						</>



					) : (



						<>



							<button onClick={saveService}>

								Save

							</button>



							<button onClick={cancelEdit}>

								Cancel

							</button>



						</>



					)}



					<button onClick={() => navigate("/services")}>

						Back

					</button>



				</div>



			</div>



		</div>



	);



}
