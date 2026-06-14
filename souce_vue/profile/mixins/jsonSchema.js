import { Ajv } from './ajv.min.js';

const ajv = new Ajv();
const schema = {
  type: 'object',
  properties: {
    data:{},
    error:{type: ['string','null']}
  },
  required: ['data','error'],
  additionalProperties: false
}

export const validJSON = ajv.compile(schema);