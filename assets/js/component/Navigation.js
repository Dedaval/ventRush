import { createNavigation } from "../utils/function.js";

export default function navigation(container){
    container.innerHTML = "";
    createNavigation(container, NAVIGATION, isLogin());
}
