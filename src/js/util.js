/** @type {(ms: number) => Promise<void>} */
export const sleep = async (ms) => {
    if (ms <= 0) {
        return;
    }

    await new Promise((done) => setTimeout(done, ms));
}

/** @type {(id: any, fn: () => any) => void} */
export const runOnceWhenDone = (() => {
    const timeouts = new Map();

    return (id, fn) => {
        if (timeouts.get(id)) {
            return;
        }

        timeouts.set(id, setTimeout(() => {
            try {
                fn();
            } finally {
                timeouts.delete(id);
            }
        }));
    }
})();