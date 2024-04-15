document.addEventListener("DOMContentLoaded", function () {
	//Navbar

	let scrollpos = window.scrollY;

	const header = document.querySelector(".navbar.main-navigation");

	let scrollChange = 50;

	const add_class_on_scroll = () => header.classList.add("bg-primary-500");

	const remove_class_on_scroll = () =>
		header.classList.remove("bg-primary-500");
	const remove_class_on_scroll_2 = () => topHeader.classList.remove("hide");

	window.addEventListener("scroll", function () {
		scrollpos = window.scrollY;
		//console.log(scrollpos);

		if (scrollpos >= scrollChange) {
			add_class_on_scroll();
		} else {
			remove_class_on_scroll();
		}
	});
});
