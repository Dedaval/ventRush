import { createNavigation } from "../utils/fonction.js";

export default function navigation(container){
    container.innerHTML = "";
    createNavigation(container, NAVIGATION, isLogin());
}
