import { NAVIGATION } from "../utils/constants.js";
import { createNavigation, isLogin } from "../utils/function.js";

export default function navigation(container){
    container.innerHTML = "";
    createNavigation(container, NAVIGATION, isLogin());
}
