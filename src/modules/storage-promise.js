/**
 * @typedef {(
 *   <R>(
 *     namespace: string,
 *     runner: (state: State) => Promise<R>,
 *   ) => Promise<R>
 * )} createSessionOrLocalPromise
 */

import { runOnceWhenDone } from "#nccr/util";

class Keys {
    /** @type {string} */ namespace;

    constructor(namespace) {
        this.namespace = namespace;
    }

    get status() {
        return `${this.namespace}_status`;
    }

    get state() {
        return `${this.namespace}_state`;
    }

    get value() {
        return `${this.namespace}_value`;
    }
}

class State extends Map {
    /** @type {Keys} */ keys;
    /** @type {Storage} */ storage;

    /**
     * 
     * @param {Keys} keys
     * @param {Storage} storage
     */
    constructor(keys, storage) {
        super(JSON.parse(storage.getItem(keys.state)) ?? []);
        this.keys = keys;
        this.storage = storage;
    }

    clear() {
        super.clear();
        this.save();
    }

    delete(key) {
        const res = super.delete(key);
        this.save();
        return res;
    }

    set(key, value) {
        const res = super.set(key, value);
        this.save();
        return res;
    }

    save() {
        runOnceWhenDone(this, () => {
            this.storage.setItem(this.keys.state, JSON.stringify([...this.entries()]));
        });
    }
}

/** @type {(storage: Storage) => createSessionOrLocalPromise} */
const createStoragePromise = (storage) =>
    (namespace, runner) => {
        const { promise, resolve, reject } = Promise.withResolvers();
        const keys = new Keys(namespace);

        const loadPromise = () => {
            switch (storage.getItem(keys.status)) {
                case "fulfilled":
                    resolve(JSON.parse(storage.getItem(keys.value)));
                    return true;
                case "rejected":
                    reject(new Error(storage.getItem(keys.value)));
                    return true;
            }

            return false;
        };

        globalThis.navigator.locks.request(namespace, async () => {
            if (loadPromise()) {
                return;
            }

            try {
                const state = new State(keys, storage);
                const value = await runner(state);

                storage.setItem(keys.value, JSON.stringify(value));
                storage.setItem(keys.status, "fulfilled");
            } catch (error) {
                storage.setItem(keys.value, error.message);
                storage.setItem(keys.status, "rejected");
            }

            loadPromise();
        });

        return promise;
    };

export const createLocalPromise = createStoragePromise(globalThis.localStorage);
export const createSessionPromise = createStoragePromise(globalThis.sessionStorage);