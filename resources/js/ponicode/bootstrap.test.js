const rewire = require("rewire")
const bootstrap = rewire("../bootstrap")
const getCookie = bootstrap.__get__("getCookie")
// @ponicode
describe("getCookie", () => {
    test("0", () => {
        let result = getCookie("=")
        expect(result).toMatchSnapshot()
    })

    test("1", () => {
        let result = getCookie("Baziz")
        expect(result).toMatchSnapshot()
    })

    test("2", () => {
        let result = getCookie("Al Saud")
        expect(result).toMatchSnapshot()
    })

    test("3", () => {
        let result = getCookie("Zong")
        expect(result).toMatchSnapshot()
    })

    test("4", () => {
        let result = getCookie("Murray-Kynynmound")
        expect(result).toMatchSnapshot()
    })

    test("5", () => {
        let result = getCookie("")
        expect(result).toMatchSnapshot()
    })
})
